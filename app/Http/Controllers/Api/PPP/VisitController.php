<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Requests\PPP\StoreVisitRequest;
use App\Http\Requests\PPP\UpdateVisitRequest;
use App\Models\Visit;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $size         = $request->input('size', 10);
        $frontendPage = $request->input('page', 0);
        $practiceId   = $request->input('practice_id');
        $isAdmin      = Auth::user()->hasRole('Admin');

        $query = Visit::with('practice:id,name_empresa', 'user:id,name,last_name,code');

        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        if ($practiceId) {
            $query->where('practice_id', $practiceId);
        }

        $data = $query->paginate($size, ['*'], 'page', $frontendPage + 1);

        return $this->successResponse([
            'content'       => $data->items(),
            'totalElements' => $data->total(),
            'currentPage'   => $frontendPage,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    public function show($id)
    {
        $visit = Visit::with('practice:id,name_empresa', 'user:id,name,last_name,code')->find($id);

        if (!$visit) {
            return $this->errorResponse('Visita no encontrada', 404);
        }

        return $this->successResponse($visit->toArray());
    }

    public function store(StoreVisitRequest $request)
    {
        $data            = $request->validated();
        $data['user_id'] = Auth::id();

        $visit = Visit::create($data);

        return $this->successResponse($visit->toArray(), 'Visita registrada correctamente', 201);
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
}
