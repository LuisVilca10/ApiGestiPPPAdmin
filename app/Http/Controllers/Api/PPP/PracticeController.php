<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Practice;
use Illuminate\Http\Request;

class PracticeController
{
    public function index()
    {
        $practices = Practice::all();
        return response()->json($practices);
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
        $request->validate([
            'name_empresa' => 'required|string',
            'document_type' => 'required|string',
            'document_path' => 'required|string',
            'status' => 'required|string',
            // Validar otros campos...
        ]);

        $practice = Practice::create($request->all());

        return response()->json(['message' => 'Practice created successfully', 'data' => $practice], 201);
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
