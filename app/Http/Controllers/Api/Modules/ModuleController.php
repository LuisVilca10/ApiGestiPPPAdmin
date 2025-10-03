<?php

namespace App\Http\Controllers\Api\Modules;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ParentModule;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
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

        $query = Module::query(); // si no necesitas el padre en la respuesta

        // Si quieres igualmente cargar el padre (aunque no lo devolvamos), puedes dejar:
        // $query = Module::with('parentModule');

        // Búsqueda tipo "index": varios campos, agrupados en un closure
        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                $q->where('title', 'like', "%{$name}%")
                    ->orWhere('subtitle', 'like', "%{$name}%")
                    ->orWhere('code', 'like', "%{$name}%")
                    ->orWhere('type', 'like', "%{$name}%")
                    ->orWhere('link', 'like', "%{$name}%");
            });
        }

        // Paginación estilo listPaginate (sin ajustar page aquí; Laravel usa 1-based)
        $data = $query->paginate($size);

        // Contenido estilo listPaginate con fechas ISO y status como int
        $content = $data->getCollection()->map(function ($module) {
            return [
                'id'          => $module->id,
                'title'       => $module->title,
                'code'        => $module->code,
                'subtitle'    => $module->subtitle,
                'type'        => $module->type,
                'icon'        => $module->icon,
                'status'      => (int) $module->status,
                'moduleOrder' => $module->moduleOrder,
                'link'        => $module->link,
                'createdAt'   => optional($module->created_at)->toISOString(),
                'updatedAt'   => optional($module->updated_at)->toISOString(),
                'deletedAt'   => $module->deleted_at ? optional($module->deleted_at)->toISOString() : null,
            ];
        })->values();

        // Respuesta exactamente como listPaginate (currentPage en 0-based)
        return response()->json([
            'totalPages'    => $data->lastPage(),
            'currentPage'   => $data->currentPage() - 1,
            'totalElements' => $data->total(),
            'content'       => $content,
        ]);
    }


    /**
     * GET /module/menu
     * Simula lo que sería un DTO de tipo menú
     */
    public function menu()
    {

        Log::info("Menu API llamado");

        $user = Auth::user();
        Log::info("Usuario autenticado:", ['user' => $user ? $user->id : null]);

        if (!$user) {
            Log::warning("Usuario no autenticado intenta acceder al menu");
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        // Verificar que el método roles existe
        if (!method_exists($user, 'roles')) {
            Log::error("El método roles() NO existe en el modelo User");
            return response()->json(['message' => 'Error interno: roles no definidos en usuario'], 500);
        } else {
            Log::info("Método roles() existe en User");
        }

        // Obtener IDs de los roles del usuario
        try {
            $userRoleIds = $user->roles->pluck('id')->toArray();
            Log::info("Roles del usuario obtenidos:", ['role_ids' => $userRoleIds]);
        } catch (\Exception $e) {
            Log::error("Error al obtener roles del usuario: " . $e->getMessage());
            return response()->json(['message' => 'Error al obtener roles del usuario'], 500);
        }

        // Consulta con filtro por roles
        try {
            // Filtramos los módulos que el usuario puede ver según sus roles
            $modules = ParentModule::whereHas('modules.roles', function ($query) use ($userRoleIds) {
                $query->whereIn('roles.id', $userRoleIds);
            })
                ->with(['modules' => function ($query) use ($userRoleIds) {
                    $query->whereHas('roles', function ($q) use ($userRoleIds) {
                        $q->whereIn('roles.id', $userRoleIds);
                    });
                }])->get();

            Log::info("Módulos obtenidos:", ['count' => $modules->count()]);
        } catch (\Exception $e) {
            Log::error("Error al obtener módulos filtrados por roles: " . $e->getMessage());
            return response()->json(['message' => 'Error al obtener módulos'], 500);
        }

        // Formatear los módulos para el menú
        $menu = $modules->map(function ($parent) {
            return [
                'id' => $parent->id,
                'title' => $parent->title,
                'subtitle' => $parent->subtitle,
                'type' => $parent->type,
                'icon' => $parent->icon,
                'link' => $parent->link,
                'moduleOrder' => $parent->moduleOrder,
                'createdAt' => $parent->created_at,
                'updatedAt' => $parent->updated_at,
                'deletedAt' => $parent->deleted_at,
                'children' => $parent->modules->map(function ($mod) {
                    return [
                        'id' => $mod->id,
                        'title' => $mod->title,
                        'subtitle' => $mod->subtitle,
                        'type' => $mod->type,
                        'icon' => $mod->icon,
                        'link' => $mod->link,
                        'moduleOrder' => $mod->moduleOrder,
                        'createdAt' => $mod->created_at,
                        'updatedAt' => $mod->updated_at,
                        'deletedAt' => $mod->deleted_at,
                    ];
                }),
            ];
        });

        Log::info("Menú formateado, listo para enviar.");

        return response()->json($menu);
    }

    /**
     * POST /parent-module
     */
    public function store(Request $request)
    {
        // Validar solo los campos que vienen del cliente
        $validated = $request->validate([
            'title'    => 'required|string|max:100',
            'code'     => 'required|string|max:10',
            'subtitle' => 'required|string|max:100',
            'status'   => 'required|boolean',
        ]);

        // Calcular el próximo orden automáticamente
        $nextOrder = (int) (ParentModule::max('moduleOrder') ?? 0) + 1;

        // Defaults que SIEMPRE se rellenan en el backend
        $defaults = [
            'type'        => 'collapsable',
            'icon'        => 'heroicons_outline:user-group',
            'moduleOrder' => $nextOrder,
            'link'        => '/example',
        ];

        // Merge entre lo que manda el cliente y lo que define el backend
        $payload = array_merge($defaults, Arr::only($validated, [
            'title',
            'code',
            'subtitle',
            'status'
        ]));

        $module = ParentModule::create($payload);

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
        $modules = Module::where('parent_module_id', $parentModuleId)->get();

        $response = $modules->map(function ($mod) {
            return [
                'id' => $mod->id,
                'title' => $mod->title,
                'selected' => false,
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
        $role = \App\Models\Role::with('modules')->findOrFail($roleId);

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

        $request->merge([
            'parent_module_id' => $request->input('parentModuleId')
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'subtitle' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'code' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            'moduleOrder' => 'required|integer',
            'link' => 'required|string|max:500',
            'parent_module_id' => 'required|uuid|exists:parent_modules,id',
        ]);

        $module->update($validated);

        return response()->json($module);
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

    /**
     * POST /module/assign
     * Asigna módulos a un rol
     */
    public function assignModulesToRole(Request $request)
    {
        $validated = $request->validate([
            'roleId'   => 'required|exists:roles,id',
            'modules'  => 'required|array',
            'modules.*' => 'exists:modules,id',
        ]);

        $role = \App\Models\Role::findOrFail($validated['roleId']);

        // Sincroniza los módulos con el rol (elimina anteriores y guarda los nuevos)
        $role->modules()->sync($validated['modules']);

        return response()->json([
            'message' => 'Módulos asignados correctamente',
            'role'    => $role->name,
            'modules' => $role->modules()->get(['id', 'title']),
        ]);
    }
}
