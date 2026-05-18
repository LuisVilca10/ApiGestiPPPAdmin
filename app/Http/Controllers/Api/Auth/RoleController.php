<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Role\AssignModulesToRoleRequest;
use App\Http\Requests\Role\AssignRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class RoleController
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        $pageSize = $request->input('size', 10);
        $page     = $request->input('page', 0);
        $name     = $request->input('name');

        $query = Role::query();

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $roles = $query->paginate($pageSize, ['*'], 'page', $page + 1);

        return $this->successResponse([
            'content'       => $roles->items(),
            'totalElements' => $roles->total(),
            'currentPage'   => $roles->currentPage() - 1,
            'totalPages'    => $roles->lastPage(),
        ]);
    }


    public function show($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        return $this->successResponse(['role' => $role]);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);

        return $this->successResponse(['role' => $role], 'Rol creado exitosamente', 201);
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        $role->update(['name' => $request->name]);

        return $this->successResponse(['role' => $role], 'Rol actualizado exitosamente');
    }


    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        $role->delete();

        return $this->successResponse([], 'Rol eliminado exitosamente');
    }

    public function assignRole(AssignRoleRequest $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->errorResponse('Usuario no encontrado', 404);
        }

        $role = Role::where('name', $request->role)->first();

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        $user->assignRole($role->name);

        return $this->successResponse([
            'user' => $user,
            'role' => $role->name,
        ], 'Rol asignado exitosamente');
    }

    public function removeRole(AssignRoleRequest $request, $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return $this->errorResponse('Usuario no encontrado', 404);
        }

        $role = Role::where('name', $request->role)->first();

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        if (!$user->hasRole($role->name)) {
            return $this->errorResponse('El usuario no tiene ese rol asignado', 422);
        }

        $user->removeRole($role->name);

        return $this->successResponse([
            'user' => $user,
            'role' => $role->name,
        ], 'Rol removido exitosamente');
    }

    public function assignModulesToRole(AssignModulesToRoleRequest $request, $roleId)
    {
        $role = Role::find($roleId);

        if (!$role) {
            return $this->errorResponse('Rol no encontrado', 404);
        }

        $modules = Module::whereIn('id', $request->modules)->get();

        if ($modules->isEmpty()) {
            return $this->errorResponse('Módulos no encontrados', 422);
        }

        $alreadyAssignedIds = $role->modules()->pluck('modules.id')->toArray();
        $newModuleIds       = $modules->pluck('id')->diff($alreadyAssignedIds)->values();

        if ($newModuleIds->isEmpty()) {
            return $this->errorResponse('Todos los módulos enviados ya están asignados a este rol.', 422);
        }

        $role->modules()->syncWithoutDetaching($newModuleIds->toArray());

        return $this->successResponse([
            'role'              => $role,
            'modules_asignados' => $newModuleIds,
        ], 'Módulos asignados exitosamente');
    }
}
