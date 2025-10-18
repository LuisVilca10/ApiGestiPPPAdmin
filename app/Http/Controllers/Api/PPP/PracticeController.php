<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Practice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PracticeController
{
    public function index(Request $request)
    {

        $size = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $laravelPage = $frontendPage + 1;

        $search = $request->input('search');

        $query = Practice::withCount('documents');

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
        // 1. Validar los datos, INCLUYENDO user_id como campo requerido
        $validatedData = $request->validate([
            'name_empresa' => 'required|string|max:255',
            'ruc' => 'required|string|size:11|regex:/^[0-9]+$/|',
            'name_represent' => 'required|string|max:255',
            'lastname_represent' => 'required|string|max:255',
            'trate_represent' => 'nullable|string|max:50',
            'phone_represent' => 'required|string|max:20',
            'activity_student' => 'required|string|max:500',
            'hourse_practice' => 'required|integer|min:1',
            // ⭐ ¡CLAVE! Validar que user_id esté en el JSON
            'user_id' => 'required|integer|exists:users,id',
        ]);
        // 2. Crear el registro
        // Usamos $validatedData, que ya contiene todos los campos, incluido user_id.
       
         $practice = Practice::create($validatedData);

        $estudiante = $practice->user;

        $data = [
            // Datos del Estudiante (Modelo User)
            'estudiante' => $estudiante,
            // Datos de la Empresa/Práctica (Modelo Practice)
            'empresa' => $practice,
            // Datos Fijos del Documento (De la imagen)
            'fecha_emision' => now()->locale('es')->isoFormat('D [de] MMMM'),
            'destinatario_nombre' => 'Mg. Amed Vargas Martínez',
            'destinatario_titulo' => 'Director de la EP Administración',
            // Puedes usar una numeración dinámica o manual para la carta
            'numero_carta' => 'CARTA N° ' . (Practice::count() + 1) . '-2025 /IS-FIA-UPEU-CJ',
        ];

        $pdf = Pdf::loadView('pdfs.carta_presentacion', $data);

        // 5. Devolver la respuesta de DESCARGA
        $nombre_archivo = 'CARTA_PRESENTACION_' . $estudiante->codigo_universitario . '.pdf';

        // 3. Devolver la respuesta
        return $pdf->download($nombre_archivo);
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
