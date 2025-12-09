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
            $fileName = 'CARTA_PRESENTACION_' . $estudiante->codigo_universitario . '_' . $fecha_formateada . '.pdf';
            $filePath = 'practicas/' . $fileName;


            Storage::disk('public')->put($filePath, $pdf->output());
            $publicUrl = Storage::url($filePath);

            Document::create([
                'practice_id' => $practice->id,
                'document_type' => 'Carta Presentacion',
                'document_path' => $filePath,
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
