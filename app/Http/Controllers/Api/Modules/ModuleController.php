<?php

namespace App\Http\Controllers\Api\Modules;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ParentModule;
use App\Models\Role;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleController
{
    use ApiResponseTrait;

    /**
     * GET /module?page=&size=&name=
     */
    public function index(Request $request)
    {
        $size = (int) $request->input('size', 10);
        $name = $request->input('name');

        // Cargamos el parentModule (y opcionalmente limitamos columnas)
        $query = Module::with('parentModule');

        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                $q->where('title', 'like', "%{$name}%")
                    ->orWhere('subtitle', 'like', "%{$name}%")
                    ->orWhere('code', 'like', "%{$name}%")
                    ->orWhere('type', 'like', "%{$name}%")
                    ->orWhere('link', 'like', "%{$name}%");
            });
        }

        $data = $query->paginate($size);

        $content = $data->getCollection()->map(function ($module) {
            $parent = $module->parentModule ? [
                'id'          => $module->parentModule->id,
                'title'       => $module->parentModule->title,
                'subtitle'    => $module->parentModule->subtitle,
                'type'        => $module->parentModule->type,
                'code'        => $module->parentModule->code,
                'icon'        => $module->parentModule->icon,
                'status'      => $module->parentModule->status,
                'moduleOrder' => $module->parentModule->moduleOrder,
                'link'        => $module->parentModule->link,
                'created_at'   => optional($module->parentModule->created_at)->toISOString(),
                'updated_at'   => optional($module->parentModule->updated_at)->toISOString(),
                'deleted_at'   => $module->parentModule->deleted_at ? optional($module->parentModule->deleted_at)->toISOString() : null,
            ] : null;

            return [
                'id'          => $module->id,
                'title'       => $module->title,
                'code'        => $module->code,
                'subtitle'    => $module->subtitle,
                'type'        => $module->type,
                'icon'        => $module->icon,
                'status'      => $module->status,
                'moduleOrder' => $module->moduleOrder,
                'link'        => $module->link,
                'parentModule' => $parent,
                'created_at'   => optional($module->created_at)->toISOString(),
                'updated_at'   => optional($module->updated_at)->toISOString(),
                'deleted_at'   => $module->deleted_at ? optional($module->deleted_at)->toISOString() : null,
            ];
        })->values();

        return response()->json([
            'totalPages'    => $data->lastPage(),
            'currentPage'   => $data->currentPage() - 1, // 0-based
            'totalElements' => $data->total(),
            'content'       => $content,
        ]);
    }



    /**
     * GET /module/menu
     * Devuelve el menú de módulos basado en los roles del usuario autenticado
     */
    public function menu()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        if (!method_exists($user, 'roles')) {
            return response()->json(['message' => 'Error interno: roles no definidos en usuario'], 500);
        }

        try {
            $userRoleIds = $user->roles->pluck('id')->toArray();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener roles del usuario'], 500);
        }

        try {
            $modules = ParentModule::query()
                ->where('status', 1)                 // si quieres solo activos
                ->where('type', 'collapsable')       // si tus padres siempre son 'collapsable'
                ->with(['modules' => function ($query) use ($userRoleIds) {
                    $query
                        // ->where('status', 1)          // (opcional) si manejas estado en hijos
                        ->whereHas('roles', function ($q) use ($userRoleIds) {
                            $q->whereIn('roles.id', $userRoleIds);
                        })
                        ->orderBy('moduleOrder');
                }])
                ->orderBy('moduleOrder')
                ->get();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener módulos'], 500);
        }

        // Formatear los módulos para el menú
        $menu = $modules->map(function ($parent) {
            return [
                'id'          => $parent->id,
                'title'       => $parent->title,
                'subtitle'    => $parent->subtitle,
                'type'        => $parent->type,
                'icon'        => $parent->icon,
                'link'        => $parent->link,
                'moduleOrder' => $parent->moduleOrder,
                'createdAt'   => $parent->created_at,
                'updatedAt'   => $parent->updated_at,
                'deletedAt'   => $parent->deleted_at,
                'children'    => $parent->modules->map(function ($mod) {
                    return [
                        'id'          => $mod->id,
                        'title'       => $mod->title,
                        'subtitle'    => $mod->subtitle,
                        'type'        => $mod->type,
                        'icon'        => $mod->icon,
                        'link'        => $mod->link,
                        'moduleOrder' => $mod->moduleOrder,
                        'created_at'  => $mod->created_at,
                        'updated_at'  => $mod->updated_at,
                        'deleted_at'  => $mod->deleted_at,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json($menu);
    }


    /**
     * POST /module
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:100',
            'subtitle'       => 'required|string|max:100',
            'code'           => 'nullable|string|max:100|unique:modules,code',
            'status'         => 'required|integer',
            'link'           => 'required|string|max:500',
            'parentModuleId' => 'required|integer|exists:parent_modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parentId = (int) $request->input('parentModuleId');

        $module = DB::transaction(function () use ($request, $parentId) {
            // Bloqueo para evitar condiciones de carrera al calcular el order
            $lastOrder = Module::where('parent_module_id', $parentId)
                ->lockForUpdate()
                ->max('moduleOrder');

            $nextOrder = $lastOrder ? ($lastOrder + 1) : 1;

            return Module::create([
                'title'            => $request->input('title'),
                'subtitle'         => $request->input('subtitle'),
                'code'             => $request->input('code'),
                'status'           => (int) $request->input('status'),
                'link'             => $request->input('link'),
                'parent_module_id' => $parentId,
                'moduleOrder'      => $nextOrder,
                // Defaults forzados
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:folder-open',
            ]);
        });

        return response()->json($module, 201);
    }




    /**
     * GET /module/{id}
     */
    public function show($id)
    {
        $module = Module::with('parentModule')->findOrFail($id);

        $formattedModule = [
            'id' => $module->id,
            'title' => $module->title,
            'subtitle' => $module->subtitle,
            'type' => $module->type,
            'code' => $module->code,
            'icon' => $module->icon,
            'status' => $module->status,
            'moduleOrder' => $module->moduleOrder,
            'link' => $module->link,
            'createdAt' => $module->created_at,
            'updatedAt' => $module->updated_at,
            'deletedAt' => $module->deleted_at,
            'parentModule' => [
                'id' => $module->parentModule->id,
                'title' => $module->parentModule->title,
                'code' => $module->parentModule->code,
                'subtitle' => $module->parentModule->subtitle,
            ]
        ];

        return response()->json($formattedModule);
    }

    /**
     * GET /module/modules-selected/roleId/{roleId}/parentModuleId/{parentModuleId}
     */
    public function modulesSelected($roleId, $parentModuleId)
    {
        $role = Role::with('modules')->findOrFail($roleId);
        $selectedIds = $role->modules->pluck('id')->toArray();

        $modules = Module::where('parent_module_id', $parentModuleId)
            ->orderBy('moduleOrder')
            ->get();

        $response = $modules->map(function ($mod) use ($selectedIds) {
            return [
                'id'       => $mod->id,
                'title'    => $mod->title,
                'selected' => in_array($mod->id, $selectedIds),
            ];
        });

        return response()->json($response);
    }


    /**
     * GET /module/role/{roleId}
     * Devuelve todos los módulos asignados a un rol
     */
    public function getModulesByRole($roleId)
    {
        $role = Role::with('modules')->findOrFail($roleId);

        return response()->json([
            'role'    => $role->name,
            'modules' => $role->modules->map(function ($mod) {
                return [
                    'id'    => $mod->id,
                    'title' => $mod->title,
                    'code'  => $mod->code,
                ];
            })
        ]);
    }


    /**
     * PUT /module/{id}
     */
    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'          => 'required|string|max:100',
            'subtitle'       => 'required|string|max:100',
            'code'           => 'nullable|string|max:100|unique:modules,code,' . $id,
            'status'         => 'required|integer',
            'link'           => 'required|string|max:500',
            'parentModuleId' => 'required|integer|exists:parent_modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldParentId = (int) $module->parent_module_id;
        $newParentId = (int) $request->input('parentModuleId');

        // Calcula nuevo order SOLO si cambia de padre
        if ($newParentId !== $oldParentId) {
            $updated = DB::transaction(function () use ($request, $module, $newParentId) {
                $lastOrder = Module::where('parent_module_id', $newParentId)
                    ->lockForUpdate()
                    ->max('moduleOrder');
                $nextOrder = $lastOrder ? ($lastOrder + 1) : 1;

                $module->update([
                    'title'            => $request->input('title'),
                    'subtitle'         => $request->input('subtitle'),
                    'code'             => $request->input('code'),
                    'status'           => (int) $request->input('status'),
                    'link'             => $request->input('link'),
                    'parent_module_id' => $newParentId,
                    'moduleOrder'      => $nextOrder, // solo cambia si cambió el padre
                    'type'             => 'basic',
                    'icon'             => 'heroicons_outline:folder-open',
                ]);

                return $module->fresh('parentModule');
            });

            return response()->json($updated);
        }

        // Si NO cambió el padre: conservar el moduleOrder actual (no tocarlo)
        $module->update([
            'title'            => $request->input('title'),
            'subtitle'         => $request->input('subtitle'),
            'code'             => $request->input('code'),
            'status'           => (int) $request->input('status'),
            'link'             => $request->input('link'),
            'parent_module_id' => $newParentId,
            'type'             => 'basic',
            'icon'             => 'heroicons_outline:folder-open',
        ]);

        return response()->json($module->fresh('parentModule'));
    }

    /**
     * DELETE /module/{id}
     */
    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        $data = Module::paginate(20);
        return response()->json($data);
    }
}
