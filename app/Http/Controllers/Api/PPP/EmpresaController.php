<?php

namespace App\Http\Controllers\Api\PPP;

use App\Http\Requests\PPP\StoreEmpresaRequest;
use App\Http\Requests\PPP\UpdateEmpresaRequest;
use App\Http\Resources\PPP\EmpresaResource;
use App\Models\Empresa;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class EmpresaController
{
    use ApiResponseTrait;

    /**
     * GET /empresa?page=&size=&search=
     * Lista paginada — solo Admin
     */
    public function index(Request $request)
    {
        $size   = $request->input('size', 10);
        $page   = max((int) $request->input('page', 0), 0);
        $search = $request->input('search');

        $query = Empresa::withCount('practices');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ruc', 'like', "%{$search}%")
                  ->orWhere('name_empresa', 'like', "%{$search}%")
                  ->orWhere('name_represent', 'like', "%{$search}%")
                  ->orWhere('lastname_represent', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('name_empresa')->paginate($size, ['*'], 'page', $page + 1);

        return $this->successResponse([
            'content'       => EmpresaResource::collection($data->items()),
            'totalElements' => $data->total(),
            'currentPage'   => $data->currentPage() - 1,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    /**
     * GET /empresa/list
     * Lista completa sin paginación para selects — Admin|Estudiante
     */
    public function list()
    {
        $empresas = Empresa::orderBy('name_empresa')->get(['id', 'ruc', 'name_empresa']);

        return $this->successResponse($empresas);
    }

    /**
     * POST /empresa
     * Crear empresa — solo Admin
     */
    public function store(StoreEmpresaRequest $request)
    {
        $empresa = Empresa::create($request->validated());

        return $this->successResponse(new EmpresaResource($empresa), 'Empresa creada correctamente', 201);
    }

    /**
     * GET /empresa/{id}
     * Ver detalle — Admin|Estudiante
     */
    public function show($id)
    {
        $empresa = Empresa::withCount('practices')->findOrFail($id);

        return $this->successResponse(new EmpresaResource($empresa));
    }

    /**
     * PUT /empresa/{id}
     * Actualizar — solo Admin
     */
    public function update(UpdateEmpresaRequest $request, $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->update($request->validated());

        return $this->successResponse(new EmpresaResource($empresa), 'Empresa actualizada correctamente');
    }

    /**
     * DELETE /empresa/{id}
     * Eliminar (soft delete) — solo Admin
     */
    public function destroy($id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();

        return $this->successResponse([], 'Empresa eliminada correctamente');
    }
}
