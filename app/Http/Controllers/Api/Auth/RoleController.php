<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController
{
    public function index(Request $request)
    {
        $pageSize = $request->get('size', 10);
        $page = $request->get('page', 0);
        $name = $request->get('name');

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
        // Guard por defecto (ej: 'web') si no te lo mandan
        $guard = $request->input('guard_name', config('auth.defaults.guard', 'api'));

        // Validación: nombre único por guard (Spatie usa name+guard_name como clave lógica)
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string', 'max:255'],
            // guard_name opcional (se forzará al $guard calculado)
            'guard_name'  => ['nullable', 'string', 'max:50'],
        ]);

        // Crear role
        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => $guard,
            'description' => $validated['description'] ?? null,
        ]);

        // DTO con camelCase para el front Kotlin
        $dto = [
            'id'         => $role->id,
            'name'       => $role->name,
            'guard_name' => $role->guard_name,
            'description' => $role->description,
            'createdAt'  => $role->created_at?->toISOString(),     // "2025-10-05T21:19:28.000000Z"
            'updatedAt'  => $role->updated_at?->toISOString(),
            'deletedAt'  => null, // Si no usas SoftDeletes en roles, siempre null
        ];

        // Opción A: devolver el objeto directo (más simple para tu parser)
        return response()->json($dto, 201);

        // === Opción B: si quieres mantener la envoltura { role: {...} } ===
        // return response()->json(['role' => $dto], 201);
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
            'description' => 'nullable|string|max:500',
        ]);

        $role->update([
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'role' => $role,
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
        // Validación alineada a tu BD (IDs enteros) y con 'mode'
        $validated = $request->validate([
            'modules'   => 'required|array',
            'modules.*' => 'integer|exists:modules,id',
            'mode'      => 'sometimes|string|in:replace,add,remove',
        ]);

        // Rol
        $role = Role::find($roleId);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $mode = $validated['mode'] ?? 'replace';

        DB::transaction(function () use ($role, $validated, $mode) {
            if ($mode === 'add') {
                // Agrega sin remover lo ya asignado
                $role->modules()->syncWithoutDetaching($validated['modules']);
            } elseif ($mode === 'remove') {
                // Quita SOLO los enviados
                $role->modules()->detach($validated['modules']);
            } else {
                // replace (por defecto): reemplaza todo por lo enviado
                $role->modules()->sync($validated['modules']);
            }
        });

        // Traer módulos actuales del rol (evita ambigüedad)
        $modules = $role->modules()
            ->select('modules.id', 'modules.title', 'modules.code')
            ->orderBy('modules.title')
            ->get();

        return response()->json([
            'message' => $mode === 'add' ? 'Módulos añadidos correctamente'
                : ($mode === 'remove' ? 'Módulos removidos correctamente'
                    : 'Módulos asignados correctamente'),
            'mode'    => $mode,
            'role'    => $role->name,
            'modules' => $modules,
        ]);
    }

    public function getRoleModules(Request $request, $roleId)
    {
        $role = Role::with('modules:id')->find($roleId);
        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $parentModuleId = $request->query('parentModuleId');

        // IDs ya asignados al rol
        $assignedIds = $role->modules->pluck('id')->all();

        // Base query: todos los módulos (o filtrados por padre si viene)
        $query = Module::query()->select('id', 'title', 'parent_module_id');
        if (!empty($parentModuleId)) {
            $query->where('parent_module_id', (int) $parentModuleId);
        }

        // Orden consistente
        $modules = $query
            ->orderBy('moduleOrder')
            ->orderBy('title')
            ->get();

        // Formato único para la UI (selector con selected=true/false)
        $items = $modules->map(fn($m) => [
            'id'       => $m->id,
            'title'    => $m->title,
            'selected' => in_array($m->id, $assignedIds, true),
        ])->values();

        return response()->json([
            'role'     => $role->name,
            'items'    => $items,
        ]);
    }
}
