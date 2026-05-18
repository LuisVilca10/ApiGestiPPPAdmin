<?php

namespace App\Http\Controllers\Api\Modules;

use App\Http\Requests\Module\StoreModuleRequest;
use App\Http\Requests\Module\UpdateModuleRequest;
use App\Http\Resources\Module\MenuParentModuleResource;
use App\Http\Resources\Module\ModuleResource;
use App\Models\Module;
use App\Models\ParentModule;
use App\Models\Role;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController
{
    use ApiResponseTrait;

    /**
     * GET /module?page=&size=&name=
     */
    public function index(Request $request)
    {
        $size = $request->input('size', 10);
        $name = $request->input('name');
        $page = max((int) $request->input('page', 0), 0);

        $query = Module::with('parentModule');

        if ($name) {
            $query->where(function ($q) use ($name) {
                $q->where('title', 'like', "%$name%")
                  ->orWhere('subtitle', 'like', "%$name%")
                  ->orWhere('code', 'like', "%$name%")
                  ->orWhere('type', 'like', "%$name%")
                  ->orWhere('link', 'like', "%$name%");
            });
        }

        $data = $query->paginate($size, ['*'], 'page', $page + 1);

        return $this->successResponse([
            'content'       => ModuleResource::collection($data->items()),
            'totalElements' => $data->total(),
            'currentPage'   => $data->currentPage() - 1,
            'totalPages'    => $data->lastPage(),
        ]);
    }

    /**
     * GET /module/menu
     */
    public function menu()
    {
        $user = Auth::user();

        if (!$user) {
            return $this->errorResponse('Usuario no autenticado', 401);
        }

        try {
            $userRoleIds = $user->roles->pluck('id')->toArray();

            $modules = ParentModule::where('status', 1)
                ->whereHas('modules.roles', function ($query) use ($userRoleIds) {
                    $query->whereIn('roles.id', $userRoleIds);
                })
                ->with(['modules' => function ($query) use ($userRoleIds) {
                    $query->where('status', 1)
                        ->whereHas('roles', function ($q) use ($userRoleIds) {
                            $q->whereIn('roles.id', $userRoleIds);
                        })
                        ->orderBy('moduleOrder');
                }])
                ->orderBy('moduleOrder')
                ->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener módulos', 500);
        }

        return $this->successResponse(MenuParentModuleResource::collection($modules));
    }

    /**
     * POST /module
     */
    public function store(StoreModuleRequest $request)
    {
        $module = Module::create($request->validated());

        return new ModuleResource($module->load('parentModule'));
    }

    /**
     * GET /module/{id}
     */
    public function show($id)
    {
        $module = Module::with('parentModule')->findOrFail($id);

        return new ModuleResource($module);
    }

    /**
     * GET /module/modules-selected/roleId/{roleId}/parentModuleId/{parentModuleId}
     */
    public function modulesSelected($roleId, $parentModuleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        $assignedIds = $role->modules()->pluck('modules.id')->toArray();
        $modules     = Module::where('parent_module_id', $parentModuleId)->get();

        return $this->successResponse(
            $modules->map(fn($mod) => [
                'id'       => $mod->id,
                'title'    => $mod->title,
                'selected' => in_array($mod->id, $assignedIds),
            ])->toArray()
        );
    }

    /**
     * PUT /module/{id}
     */
    public function update(UpdateModuleRequest $request, $id)
    {
        $module = Module::findOrFail($id);
        $module->update($request->validated());

        return new ModuleResource($module->load('parentModule'));
    }

    /**
     * DELETE /module/{id}
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return $this->successResponse([], 'Módulo eliminado correctamente');
    }
}
