<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleController
{
    public function index(Request $request)
    {
        $pageSize = $request->get('size', 10);
        $page = $request->get('page', 0);
        $name = $request->get('name'); // 🔍 Variable de búsqueda

        // Construir query con filtro si hay búsqueda
        $query = Role::query();

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        // Paginar
        $roles = $query->paginate($pageSize, ['*'], 'page', $page + 1); // base 1 para Laravel

        return response()->json([
            'content' => $roles->items(),
            'totalElements' => $roles->total(),
            'currentPage' => $roles->currentPage() - 1,
            'totalPages' => $roles->lastPage()
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        return response()->json([
            'role' => $role,
            'message' => 'Rol creado exitosamente',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'Rol no encontrado',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $validated['name']]);

        return response()->json([
            'role' => $role,
            'message' => 'Rol actualizado exitosamente',
        ]);
    }


    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'message' => 'Rol no encontrado',
            ], 404);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente',
        ]);
    }

    public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
            ], 404);
        }

        $role = Role::findByName($request->role);
        $user->assignRole($role);

        return response()->json([
            'message' => 'Rol asignado exitosamente',
            'user' => $user,
            'role' => $role->name,
        ]);
    }

    public function assignModulesToRole(Request $request, $roleId)
    {
        // Validación de los módulos enviados (verificar que sean UUIDs válidos)
        $request->validate([
            'modules' => 'required|array',
            'modules.*' => 'uuid|exists:modules,id', // Validar que cada módulo sea un UUID y exista en la tabla 'modules'
        ]);

        // Buscar el rol por ID
        $role = Role::find($roleId);

        if (!$role) {

            return response()->json(['message' => 'Rol no encontrado'], 404);
        }


        // Obtener los módulos por sus IDs UUID
        $modules = Module::whereIn('id', $request->modules)->get();

        if ($modules->isEmpty()) {

            return response()->json(['message' => 'Módulos no encontrados'], 422);
        }

        // Asignar los módulos al rol
        foreach ($modules as $module) {
            // Sincronizar los módulos con el rol (sin eliminar los módulos previamente asignados)
            $role->modules()->syncWithoutDetaching([$module->id]);
        }

        // Devolver la respuesta con los módulos asignados
        return response()->json([
            'message' => 'Módulos asignados exitosamente',
            'role' => $role,
            'modules' => $modules->pluck('id'),
        ]);
    }
}
