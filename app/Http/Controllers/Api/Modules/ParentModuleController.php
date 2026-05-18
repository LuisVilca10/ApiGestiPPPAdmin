<?php

namespace App\Http\Controllers\Api\Modules;

use App\Http\Requests\ParentModule\StoreParentModuleRequest;
use App\Http\Requests\ParentModule\UpdateParentModuleRequest;
use App\Http\Resources\Module\ParentModuleResource;
use App\Models\ParentModule;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ParentModuleController
{
    use ApiResponseTrait;
    /**
     * GET /parent-module?page=&size=&name=
     */

    public function listPaginate(Request $request)
    {
        $size = $request->input('size', 10);
        $name = $request->input('name');

        $query = ParentModule::query();

        if ($name) {
            $query->where('title', 'like', "%$name%");
        }

        $data = $query->paginate($size);

        return $this->successResponse([
            'totalPages'    => $data->lastPage(),
            'currentPage'   => $data->currentPage() - 1,
            'content'       => ParentModuleResource::collection($data->items()),
            'totalElements' => $data->total(),
        ]);
    }

    /**
     * GET /parent-module/list?name=
     */
    public function list(Request $request)
    {
        $name = $request->input('name');
        $query = ParentModule::query();

        if ($name) {
            $query->where('title', 'like', "%$name%");
        }

        return $this->successResponse(ParentModuleResource::collection($query->get()));
    }

    /**
     * GET /parent-module/list-detail-module-list
     */
    public function listDetailModuleList()
    {
        $parents = ParentModule::with('modules')->get();

        return ParentModuleResource::collection($parents);
    }

    /**
     * POST /parent-module
     */
    public function store(StoreParentModuleRequest $request)
    {
        $module = ParentModule::create($request->validated());

        return (new ParentModuleResource($module))->response()->setStatusCode(201);
    }

    /**
     * GET /parent-module/{id}
     */
    public function show($id)
    {
        $module = ParentModule::findOrFail($id);

        return new ParentModuleResource($module);
    }

    /**
     * PUT /parent-module/{id}
     */
    public function update(UpdateParentModuleRequest $request, $id)
    {
        $module = ParentModule::findOrFail($id);
        $module->update($request->validated());

        return new ParentModuleResource($module);
    }

    /**
     * DELETE /parent-module/{id}
     */
    public function destroy($id)
    {
        $module = ParentModule::findOrFail($id);
        $module->delete();

        return $this->successResponse([], 'Módulo padre eliminado correctamente');
    }
}
