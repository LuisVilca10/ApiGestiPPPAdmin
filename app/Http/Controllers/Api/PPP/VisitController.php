<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Requests\PPP\StoreVisitRequest;
use App\Http\Requests\PPP\UpdateVisitRequest;
use App\Models\Practice;
use App\Models\Visit;
use App\Services\PracticeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController
{
    use ApiResponseTrait;

    public function __construct(private readonly PracticeService $practiceService) {}

    public function index(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $practiceId   = $request->input('practice_id');
        $user         = Auth::user();

        $query = Visit::with(['practice:id,empresa_id', 'practice.empresa:id,name_empresa', 'user:id,name,last_name,code']);

        if ($user->hasAnyRole(['Admin', 'Encargado de Practicas', 'Coordinador'])) {
            // Ve todas las visitas
        } elseif ($user->hasRole('Docente')) {
            // Solo ve visitas de las prácticas que le fueron asignadas
            $query->whereHas('practice', fn($q) => $q->where('docente_id', $user->id));
        } elseif ($user->hasRole('Estudiante')) {
            // Solo ve las visitas de sus propias prácticas
            $query->whereHas('practice', fn($q) => $q->where('user_id', $user->id));
        }

        if ($practiceId) {
            $query->where('practice_id', $practiceId);
        }

        $data = $query->orderBy('visit_date', 'desc')
            ->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => $data->items(),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function show($id)
    {
        $visit = Visit::with(['practice:id,empresa_id,user_id,docente_id', 'practice.empresa:id,name_empresa', 'user:id,name,last_name,code'])->find($id);

        if (!$visit) {
            return $this->errorResponse('Visita no encontrada', 404);
        }

        $user = Auth::user();

        if (!$user->hasAnyRole(['Admin', 'Encargado de Practicas', 'Coordinador'])) {
            if ($user->hasRole('Docente') && (int) $visit->practice?->docente_id !== $user->id) {
                return $this->errorResponse('No tienes acceso a esta visita', 403);
            }
            if ($user->hasRole('Estudiante') && (int) $visit->practice?->user_id !== $user->id) {
                return $this->errorResponse('No tienes acceso a esta visita', 403);
            }
        }

        return $this->successResponse($visit->toArray());
    }

    public function store(StoreVisitRequest $request)
    {
        $practice = Practice::with('documents')->find($request->practice_id);

        // La práctica debe estar aprobada
        if ($practice->status !== 'Aprobado') {
            return $this->errorResponse('La práctica debe estar aprobada para programar visitas.', 422);
        }

        // El Plan de Prácticas debe estar aprobado
        $planAprobado = $practice->documents()
            ->where('document_type', 'Plan de Practicas')
            ->where('document_status', 'Aprobado')
            ->exists();

        if (!$planAprobado) {
            return $this->errorResponse('El Plan de Prácticas debe estar aprobado antes de programar visitas.', 422);
        }

        // Debe tener un Docente asignado
        if (!$practice->docente_id) {
            return $this->errorResponse('Debes asignar un Docente a la práctica antes de programar visitas.', 422);
        }

        // Solo 1 visita por tipo (Inicio, Medio, Final)
        $tipoExiste = Visit::where('practice_id', $practice->id)
            ->where('visit_type', $request->visit_type)
            ->exists();

        if ($tipoExiste) {
            return $this->errorResponse("Ya existe una visita de tipo \"{$request->visit_type}\" para esta práctica.", 422);
        }

        $data                 = $request->validated();
        $data['user_id']      = $practice->docente_id; // asignado automáticamente al docente de la práctica
        $data['visit_status'] = 'Programada';

        $visit = Visit::create($data);

        return $this->successResponse(
            $visit->load(['practice:id,empresa_id', 'practice.empresa:id,name_empresa', 'user:id,name,last_name,code'])->toArray(),
            'Visita programada correctamente.',
            201
        );
    }

    public function update(UpdateVisitRequest $request, $id)
    {
        $visit = Visit::find($id);

        if (!$visit) {
            return $this->errorResponse('Visita no encontrada', 404);
        }

        $visit->update($request->validated());

        return $this->successResponse($visit->toArray(), 'Visita actualizada correctamente');
    }

    public function destroy($id)
    {
        $visit = Visit::find($id);

        if (!$visit) {
            return $this->errorResponse('Visita no encontrada', 404);
        }

        $visit->delete();

        return $this->successResponse([], 'Visita eliminada correctamente');
    }

    /**
     * Programa las 3 visitas (Inicio, Medio, Final) de una sola vez.
     * Si no se envían fechas, usa las sugeridas por start_date/end_date.
     */
    public function programarVisitas(Request $request, $practiceId)
    {
        $practice = Practice::with('documents')->find($practiceId);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status !== 'Aprobado') {
            return $this->errorResponse('La práctica debe estar aprobada.', 422);
        }

        if (!$practice->docente_id) {
            return $this->errorResponse('Debes asignar un Docente antes de programar visitas.', 422);
        }

        $planAprobado = $practice->documents()
            ->where('document_type', 'Plan de Practicas')
            ->where('document_status', 'Aprobado')
            ->exists();

        if (!$planAprobado) {
            return $this->errorResponse('El Plan de Prácticas debe estar aprobado antes de programar visitas.', 422);
        }

        $request->validate([
            'fecha_inicio' => 'nullable|date',
            'fecha_medio'  => 'nullable|date|after:fecha_inicio',
            'fecha_final'  => 'nullable|date|after:fecha_medio',
        ]);

        // Usar fechas enviadas o calcular las sugeridas automáticamente
        $sugeridas = $this->practiceService->sugerirFechasVisitas($practice);

        $fechas = [
            'Inicio' => $request->fecha_inicio ?? $sugeridas['Inicio'] ?? null,
            'Medio'  => $request->fecha_medio  ?? $sugeridas['Medio']  ?? null,
            'Final'  => $request->fecha_final  ?? $sugeridas['Final']  ?? null,
        ];

        if (in_array(null, $fechas, true)) {
            return $this->errorResponse(
                'No se pueden calcular las fechas automáticamente. Envía fecha_inicio, fecha_medio y fecha_final.',
                422
            );
        }

        $creadas  = [];
        $omitidas = [];

        foreach ($fechas as $tipo => $fecha) {
            $existe = Visit::where('practice_id', $practice->id)
                ->where('visit_type', $tipo)
                ->exists();

            if ($existe) {
                $omitidas[] = $tipo;
                continue;
            }

            $creadas[] = Visit::create([
                'practice_id'  => $practice->id,
                'user_id'      => $practice->docente_id,
                'visit_type'   => $tipo,
                'visit_date'   => $fecha,
                'visit_status' => 'Programada',
            ]);
        }

        return $this->successResponse([
            'creadas'  => $creadas,
            'omitidas' => $omitidas,
            'fechas'   => $fechas,
        ], count($creadas) . ' visita(s) programada(s) correctamente.');
    }
}
