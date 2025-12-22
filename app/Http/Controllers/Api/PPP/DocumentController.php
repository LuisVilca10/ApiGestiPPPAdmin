<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController
{
    // Método GET: Obtener todos los documentos
    public function indexForStudent(Request $request)
    {
        $size = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $laravelPage = $frontendPage + 1;
        $search = $request->input('search');

        // 1. Obtenemos el ID del usuario autenticado
        $userId = Auth::id();

        // 2. Construimos la consulta con el filtro por usuario
        $query = Document::with(['practice' => function ($q) {
            $q->select('id', 'name_empresa');
        }])->whereHas('practice', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        if ($search) {
            $query->where('document_name', 'LIKE', '%' . $search . '%');
        }


        $data = $query->orderBy('created_at', 'desc')
            ->paginate($size, ['*'], 'page', $laravelPage);

        // 2. Transformamos la colección para que el nombre de la práctica esté al primer nivel si lo deseas
        $items = collect($data->items())->map(function ($doc) {
            return [
                'id' => $doc->id,
                'document_type' => $doc->document_type,
                'document_name' => $doc->document_name,
                'document_path' => $doc->document_path,
                'document_status' => $doc->document_status,
                'practice_name' => $doc->practice->name_empresa ?? 'Sin práctica', // El nombre en vez del ID
                'created_at' => $doc->created_at,
            ];
        });
        return response()->json([
            'content' => $items,
            'totalElements' => $data->total(),
            'currentPage' => $frontendPage,
            'totalPages' => $data->lastPage(),
        ]);
    }
    // Método POST: Subir un documento para una práctica específica
    public function storeDocumentBitacora(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $request->validate([
            'practice_id' => 'required|exists:practices,id',
            'document_type' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:30720', // Máximo 30MB
        ]);
        $username = Auth::user()->code;
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        // Generar nombre descriptivo
        $archivoNombre = $request->document_type . ' - ' . $username . ' - ' . now()->format('dmYHi') . '.' . $extension;

        // 2. Usamos storeAs en lugar de store
        // 'practicas' es la carpeta, $archivoNombre el nombre, 'public' el disco
        $path = $file->storeAs('practicas', $archivoNombre, 'public');

        $document = Document::create([
            'practice_id' => $request->practice_id,
            'document_type' => $request->document_type,
            'document_name' => $archivoNombre, // Corregido: sin la 'a' extra
            'document_path' => $path,
            'document_status' => "En Proceso",
        ]);

        $document->load('practice');

        $dataResponse = [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_name' => $document->document_name,
            'document_path' => $document->document_path,
            'document_status' => $document->document_status,
            'practice_name' => $document->practice->name_empresa ?? 'Empresa no asignada',
            'created_at' => $document->created_at->toISOString(), // Formato ISO compatible con Kotlin
        ];

        // Devolvemos el objeto creado con código 201
        return response()->json([
            'status' => 'success',
            'message' => 'Document uploaded successfully',
            'data' => $dataResponse
        ], 201);
    }

    // Método GET: Obtener un documento específico por ID
    public function show($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return response()->json($document);
    }

    // Método POST: Crear un nuevo documento
    public function store(Request $request)
    {
        $request->validate([
            'practice_id' => 'required|exists:practices,id',  // Relación con la práctica
            'document_type' => 'required|string',
            'document_path' => 'required|string',
            'status' => 'required|string',
            // Validar otros campos si es necesario
        ]);

        // Crear el nuevo documento
        $document = Document::create([
            'practice_id' => $request->practice_id,
            'document_type' => $request->document_type,
            'document_path' => $request->document_path,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Document created successfully', 'data' => $document], 201);
    }

    // Método PUT: Actualizar un documento existente
    public function update(Request $request, $id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document->update([
            'document_type' => $request->document_type ?? $document->document_type,
            'document_path' => $request->document_path ?? $document->document_path,
            'status' => $request->status ?? $document->status,
        ]);

        return response()->json(['message' => 'Document updated successfully', 'data' => $document]);
    }

    // Método DELETE: Eliminar un documento
    public function destroy($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully']);
    }
}
