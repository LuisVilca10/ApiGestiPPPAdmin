<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Resources\PPP\DocumentResource;
use App\Models\Document;
use App\Models\Practice;
use App\Services\DocumentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly DocumentService $documentService,
    ) {}

    public function indexForStudent(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $search       = $request->input('search');
        $isAdmin      = Auth::user()->hasRole('Admin');

        $query = Document::with(['practice' => fn($q) => $q->select('id', 'empresa_id')->with('empresa:id,name_empresa')]);

        if (!$isAdmin) {
            $query->whereHas('practice', fn($q) => $q->where('user_id', Auth::id()));
        }

        if ($search) {
            $query->where('document_name', 'LIKE', '%' . $search . '%');
        }

        $data = $query->orderBy('created_at', 'desc')
            ->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => DocumentResource::collection($data->items()),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function aprobar($id)
    {
        $document = Document::with('practice.user')->find($id);

        if (!$document) {
            return $this->errorResponse('Documento no encontrado', 404);
        }

        if ($document->document_status === 'Aprobado') {
            return $this->errorResponse('El documento ya está aprobado', 422);
        }

        $document->update(['document_status' => 'Aprobado']);

        $this->asignarHorasSiCompleto($document->practice);

        return $this->successResponse(
            (new DocumentResource($document->fresh()->load('practice')))->resolve(),
            'Documento aprobado correctamente'
        );
    }

    public function rechazar(Request $request, $id)
    {
        $document = Document::with('practice')->find($id);

        if (!$document) {
            return $this->errorResponse('Documento no encontrado', 404);
        }

        $document->update(['document_status' => 'Rechazado']);

        return $this->successResponse(
            (new DocumentResource($document->fresh()->load('practice')))->resolve(),
            'Documento rechazado'
        );
    }

    public function destroy($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento no encontrado', 404);
        }

        $document->delete();

        return $this->successResponse([], 'Documento eliminado correctamente');
    }

    private function asignarHorasSiCompleto(Practice $practice): void
    {
        if ($practice->status === 'Cerrado') {
            return;
        }

        // El evento cierra cuando la Constancia de Practica está aprobada
        $constanciaAprobada = $practice->documents()
            ->where('document_type', 'Constancia de Practica')
            ->where('document_status', 'Aprobado')
            ->exists();

        if (!$constanciaAprobada) {
            return;
        }

        // Verificar que no haya documentos pendientes o rechazados
        $hayPendientes = $practice->documents()
            ->whereIn('document_status', ['En Proceso', 'Rechazado'])
            ->exists();

        if ($hayPendientes) {
            return;
        }

        // Cerrar el evento y acumular horas al alumno
        $practice->update(['status' => 'Cerrado']);
        $practice->user->increment('hours_of_practice', $practice->hourse_practice);
    }
}
