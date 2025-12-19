<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Document;
use App\Models\Practice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PracticeController
{
    // Método GET: Listar todas las prácticas (FALTA FILTRAR POR USUARIOS) con paginación y búsqueda
    public function index(Request $request)
    {

        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'No autorizado. Se requiere autenticación.'], 401);
        }

        $size = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $laravelPage = $frontendPage + 1;
        $search = $request->input('search');

        $query = Practice::where('user_id', $userId)->withCount('documents');

        if ($search) {
            $query->where('name_empresa', 'like', "%{$search}%")
                ->orWhere('ruc', 'like', "%{$search}%");
        }

        $data = $query->paginate($size, ['*'], 'page', $laravelPage);

        return response()->json([
            'content' => $data->items(),  // Devuelve solo los elementos de la página actual
            'totalElements' => $data->total(),
            'currentPage' => $frontendPage, // Restamos 1 para ajustarlo al formato que pides
            'totalPages' => $data->lastPage(),
        ]);
        // $practices = Practice::all();
        // return response()->json($practices);
    }

    //obtener documentos por práctica con paginación y búsqueda
    public function DocumentsByPractice(Request $request, $practiceId)
    {
        // 1. Obtener parámetros de paginación y búsqueda
        $size = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $laravelPage = $frontendPage + 1;
        $search = $request->input('search');

        // 2. Iniciar la consulta filtrando por la Práctica
        // Asumimos que tu tabla 'documents' tiene una columna 'practice_id'
        $query = Document::where('practice_id', $practiceId);

        // 3. Aplicar búsqueda (si el usuario escribió algo en el buscador)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%") // Cambia 'name' por el nombre real de tu columna
                    ->orWhere('description', 'like', "%{$search}%"); // Opcional
            });
        }

        // 4. Ejecutar la paginación
        $data = $query->paginate($size, ['*'], 'page', $laravelPage);

        $data->getCollection()->transform(function ($document) {
            $document->document_path = env("APP_URL") . 'storage/' . $document->document_path;
            return $document;
        });

        // 5. Retornar la respuesta con el formato esperado
        return response()->json([
            'content' => $data->items(),
            'totalElements' => $data->total(),
            'currentPage' => $frontendPage,
            'totalPages' => $data->lastPage(),
        ]);
    }

    // Método GET: Obtener las prácticas con id relacionaods a un usuario
    public function practicesforselect(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['message' => 'No autorizado. Se requiere autenticación.'], 401);
        }

        // 1. Obtenemos TODAS las prácticas del usuario.
        $practices = Practice::where('user_id', $userId)
            ->select('id', 'name_empresa') // Solo los campos necesarios.
            ->orderBy('name_empresa')      // Las mandamos ordenadas.
            ->get();

        // 2. Mapeamos al formato deseado (opcional pero buena práctica).
        $data = $practices->map(fn($p) => ['id' => $p->id, 'name_empresa' => $p->name_empresa]);

        // 3. Devolvemos el listado completo.<
        return response()->json(['data' => $data]);
    }


    public function show($id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return response()->json(['message' => 'Practice not found'], 404);
        }

        return response()->json($practice);
    }

    // Método POST: Crear una nueva práctica
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_empresa' => 'required|string|max:255',
                'ruc' => 'required|string|size:11|regex:/^[0-9]+$/',
                'name_represent' => 'required|string|max:255',
                'lastname_represent' => 'required|string|max:255',
                'trate_represent' => 'nullable|string|max:50',
                'phone_represent' => 'required|string|max:20',
                'activity_student' => 'required|string|max:500',
                'hourse_practice' => 'required|integer|min:1',
            ]);
            $userId = Auth::id();
            if (!$userId) {
                return response()->json(['error' => 'Token inválido o usuario no autenticado'], 401);
            }

            $validated['user_id'] = $userId;

            $practice = Practice::create($validated);
            
            $estudiante = $practice->user;

            if (!$estudiante) {
                throw new \Exception("No se encontró el estudiante (User) para la práctica ID: " . $practice->id);
            }

            $data = [
                'estudiante' => $estudiante,
                'empresa' => $practice,
                'fecha_emision' => now()->locale('es')->isoFormat('D [de] MMMM'),
                'destinatario_nombre' => 'Mg. Amed Vargas Martínez',
                'destinatario_titulo' => 'Director de la EP Administración',
                'numero_carta' => 'CARTA N° ' . (Practice::count()) . '-2025 /IS-FIA-UPEU-CJ',
            ];

            $pdf = Pdf::loadView('pdfs.carta_presentacion', $data);
            $fecha_formateada = Carbon::now()->format('dmYHi');
            $fileName = 'CARTA_PRESENTACION_' . $estudiante->code . '_' . $fecha_formateada . '.pdf';
            $filePath = 'practicas/' . $fileName;


            Storage::disk('public')->put($filePath, $pdf->output());
            $publicUrl = Storage::url($filePath);

            Document::create([
                'practice_id' => $practice->id,
                'document_type' => 'Carta Presentacion',
                'document_path' => $filePath,
                'document_name' => 'Carta Presentacion' . ' - ' . $estudiante->name,
                'document_status' => 'En Proceso',
            ]);

            $practiceData = $practice->toArray();
            $practiceData['pdf_url'] = $publicUrl;
            unset($practiceData['user']);

            return response()->json($practiceData, 201);
        } catch (\Throwable $th) {
            Log::error('❌ Error en creación de práctica:', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error interno del servidor',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    // Método POST: Subir un documento relacionado a una práctica
    public function storeDocumentPractice(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autorizado.'], 401);
        }

        $request->validate([
            'practice_id' => 'required|exists:practices,id',
            'document_type' => 'required|string',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);
        $username = Auth::user()->code;
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $archivoNombre = $request->document_type . ' - ' . $username . ' - ' . now()->format('dmYHi') . '.' . $extension;

        // 2. Usamos storeAs en lugar de store
        // 'practicas' es la carpeta, $archivoNombre el nombre, 'public' el disco
        $path = $file->storeAs('practicas', $archivoNombre, 'public');

        $document = Document::create([
            'practice_id' => $request->practice_id,
            'document_type' => $request->document_type,
            'document_name' => $archivoNombre,
            'document_path' => $path,
            'document_status' => "En Proceso",
        ]);

        // Devolvemos el objeto creado con código 201
        return response()->json([
            'status' => 'success',
            'message' => 'Document uploaded successfully',
            'data' => $document
        ], 201);
    }


    // Método PUT: Actualizar los detalles de una práctica
    public function update(Request $request, $id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return response()->json(['message' => 'Practice not found'], 404);
        }

        $practice->update($request->all());

        return response()->json(['message' => 'Practice updated successfully', 'data' => $practice]);
    }

    // Método DELETE: Eliminar una práctica
    public function destroy($id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return response()->json(['message' => 'Practice not found'], 404);
        }

        $practice->delete();

        return response()->json(['message' => 'Practice deleted successfully']);
    }
}
