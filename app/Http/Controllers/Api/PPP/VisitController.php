<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController
{
    // Método GET: Obtener todas las visitas
    public function index()
    {
        $visits = Visit::all();
        return response()->json($visits);
    }

    // Método GET: Obtener una visita específica por ID
    public function show($id)
    {
        $visit = Visit::find($id);

        if (!$visit) {
            return response()->json(['message' => 'Visit not found'], 404);
        }

        return response()->json($visit);
    }

    // Método POST: Crear una nueva visita
    public function store(Request $request)
    {
        $request->validate([
            'practice_id' => 'required|exists:practices,id',  // Relación con la práctica
            'visit_date' => 'required|date',
            'visit_type' => 'required|string',
            'visit_notes' => 'nullable|string',
            'visit_result' => 'required|integer',
        ]);

        // Crear una nueva visita
        $visit = Visit::create([
            'practice_id' => $request->practice_id,
            'visit_date' => $request->visit_date,
            'visit_type' => $request->visit_type,
            'visit_notes' => $request->visit_notes,
            'visit_result' => $request->visit_result,
        ]);

        return response()->json(['message' => 'Visit created successfully', 'data' => $visit], 201);
    }

    // Método PUT: Actualizar una visita existente
    public function update(Request $request, $id)
    {
        $visit = Visit::find($id);

        if (!$visit) {
            return response()->json(['message' => 'Visit not found'], 404);
        }

        $visit->update([
            'visit_date' => $request->visit_date ?? $visit->visit_date,
            'visit_type' => $request->visit_type ?? $visit->visit_type,
            'visit_notes' => $request->visit_notes ?? $visit->visit_notes,
            'visit_result' => $request->visit_result ?? $visit->visit_result,
        ]);

        return response()->json(['message' => 'Visit updated successfully', 'data' => $visit]);
    }

    // Método DELETE: Eliminar una visita
    public function destroy($id)
    {
        $visit = Visit::find($id);

        if (!$visit) {
            return response()->json(['message' => 'Visit not found'], 404);
        }

        $visit->delete();

        return response()->json(['message' => 'Visit deleted successfully']);
    }
}
