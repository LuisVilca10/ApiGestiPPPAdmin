<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController
{
    // Método GET: Obtener todos los documentos
    public function index()
    {
        $documents = Document::all();
        return response()->json($documents);
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
