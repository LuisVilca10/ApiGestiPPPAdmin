<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Requests\PPP\StoreDocumentRequest;
use App\Http\Requests\PPP\StorePracticeRequest;
use App\Http\Requests\PPP\UpdatePracticeRequest;
use App\Http\Resources\PPP\DocumentResource;
use App\Models\Document;
use App\Models\Practice;
use App\Services\DocumentService;
use App\Services\PracticeService;
use App\Traits\ApiResponseTrait;
use App\Mail\PracticeApproved;
use App\Mail\PracticeRejected;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PracticeController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PracticeService $practiceService,
        private readonly DocumentService $documentService,
    ) {}

    public function index(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');
        $periodo      = $request->input('periodo');
        $status       = $request->input('status');
        $isAdmin      = Auth::user()->hasRole('Admin');

        $query = Practice::withCount('documents')->with('user:id,name,last_name,code');

        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        if ($search) {
            $query->where(function ($q) use ($search, $isAdmin) {
                $q->where('name_empresa', 'like', "%{$search}%")
                  ->orWhere('ruc', 'like', "%{$search}%");

                // Admin también puede buscar por datos del estudiante
                if ($isAdmin) {
                    $q->orWhereHas('user', function ($u) use ($search) {
                        $u->where('code', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }
            });
        }

        if ($periodo) {
            $rango = Practice::rangoFechasDePeriodo($periodo);
            if ($rango) {
                $query->whereBetween('created_at', $rango);
            }
        }

        if ($status && in_array($status, ['Pendiente', 'Aprobado', 'Rechazado'])) {
            $query->where('status', $status);
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        $items = collect($data->items())->map(function ($practice) {
            $arr = $practice->toArray();
            $arr['periodo'] = Practice::calcularPeriodo($practice->created_at);
            return $arr;
        });

        return $this->successResponse([
            'content'       => $items,
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function periodos(Request $request)
    {
        $isAdmin = Auth::user()->hasRole('Admin');

        // Año más antiguo con prácticas (para este usuario o global si es admin)
        $query = Practice::selectRaw('MIN(YEAR(created_at)) as min_year');

        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        $minYear = $query->value('min_year') ?? Carbon::now()->year;

        $periodos = Practice::generarPeriodosHasta((int)$minYear);

        return $this->successResponse($periodos);
    }

    public function documentsByPractice(Request $request, $practiceId)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');

        $query = Document::where('practice_id', $practiceId);

        if ($search) {
            $query->where('document_name', 'like', "%{$search}%");
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => $data->items(),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function tiposDocumento()
    {
        return $this->successResponse(Document::TIPOS_PERMITIDOS);
    }

    public function practicesforselect(Request $request)
    {
        $practices = Practice::where('user_id', Auth::id())
            ->select('id', 'name_empresa')
            ->orderBy('name_empresa')
            ->get();

        return $this->successResponse(
            $practices->map(fn($p) => ['id' => $p->id, 'name_empresa' => $p->name_empresa])->toArray()
        );
    }

    public function show($id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        return $this->successResponse($practice->toArray());
    }

    public function store(StorePracticeRequest $request)
    {
        $validated             = $request->validated();
        $validated['user_id']  = Auth::id();
        $validated['status']   = 'Pendiente';

        $practice = Practice::create($validated);

        return $this->successResponse($practice->toArray(), 'Práctica registrada. Pendiente de aprobación.', 201);
    }

    public function approve($id)
    {
        $practice = Practice::with('user')->find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status === 'Aprobado') {
            return $this->errorResponse('La práctica ya fue aprobada', 422);
        }

        if (!$practice->user) {
            return $this->errorResponse('El estudiante asociado no existe', 422);
        }

        try {
            $practice->update(['status' => 'Aprobado', 'rejection_reason' => null]);

            $pdf = $this->practiceService->generateCartaPresentacion($practice);

            if ($practice->user->email) {
                Mail::to($practice->user->email)
                    ->send(new PracticeApproved($practice, $pdf['url'], $pdf['path']));
            }

            return $this->successResponse(
                array_merge($practice->fresh()->toArray(), ['pdf_url' => $pdf['url']]),
                'Práctica aprobada y carta de presentación generada.'
            );
        } catch (\Throwable $th) {
            Log::error('Error al aprobar práctica:', ['error' => $th->getMessage(), 'line' => $th->getLine()]);
            return $this->errorResponse('Error al generar la carta de presentación', 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status === 'Aprobado') {
            return $this->errorResponse('No se puede rechazar una práctica ya aprobada', 422);
        }

        $rejectionReason = $request->input('rejection_reason');

        $practice->update([
            'status'           => 'Rechazado',
            'rejection_reason' => $rejectionReason,
        ]);

        $practice->load('user');

        // Notificar al estudiante si tiene correo registrado
        if ($practice->user?->email) {
            Mail::to($practice->user->email)
                ->send(new PracticeRejected($practice, $rejectionReason));
        }

        return $this->successResponse($practice->toArray(), 'Práctica rechazada.');
    }

    public function storeDocumentPractice(StoreDocumentRequest $request)
    {
        $user         = Auth::user();
        $documentType = $request->document_type;
        $practice     = Practice::with('user')->find($request->practice_id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        if ($practice->status !== 'Aprobado') {
            return $this->errorResponse('Solo se pueden subir documentos a prácticas aprobadas', 422);
        }

        // Tipos reservados para Admin: docente y evaluaciones externas
        if (in_array($documentType, Document::TIPOS_ADMIN) && !$user->hasRole('Admin')) {
            return $this->errorResponse('No tienes permiso para subir este tipo de documento', 403);
        }

        // Informe de Prácticas: requiere 1500 horas completadas
        if ($documentType === 'Informe de Practicas') {
            $horasCompletadas = $practice->user->hours_of_practice ?? 0;
            if ($horasCompletadas < 1500) {
                return $this->errorResponse(
                    "El informe requiere 1500 horas completadas. El estudiante lleva {$horasCompletadas} horas.",
                    422
                );
            }
        }

        $document = $this->documentService->upload(
            $request->practice_id,
            $documentType,
            $request->file('file')
        );

        return (new DocumentResource($document->load('practice')))->response()->setStatusCode(201);
    }

    public function update(UpdatePracticeRequest $request, $id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        $practice->update($request->validated());

        return $this->successResponse($practice->toArray(), 'Práctica actualizada correctamente');
    }

    public function destroy($id)
    {
        $practice = Practice::find($id);

        if (!$practice) {
            return $this->errorResponse('Práctica no encontrada', 404);
        }

        $practice->delete();

        return $this->successResponse([], 'Práctica eliminada correctamente');
    }
}
