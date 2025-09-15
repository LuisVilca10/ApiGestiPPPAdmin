<?php

namespace App\Http\Controllers\Api\Modules;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ParentModule;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ModuleController
{
    use ApiResponseTrait;
    /**
     * GET /module?page=&size=&name=pastor
     */
    public function index(Request $request)
    {
        $size = $request->input('size', 10);
        $name = $request->input('name');
        $page = max((int) $request->input('page', 0), 0);

        $query = Module::with('parentModule');

        if ($name) {
            $query->where('title', 'like', "%$name%")
                ->orWhere('subtitle', 'like', "%$name%")
                ->orWhere('code', 'like', "%$name%")
                ->orWhere('type', 'like', "%$name%")
                ->orWhere('link', 'like', "%$name%");
        }
        $data = $query->paginate($size, ['*'], 'page', $page + 1); // <-- aquí ajustas


        $response = $data->items(); // Accedemos solo a los items de la paginación

        // Mapear la respuesta
        $response = collect($response)->map(function ($module) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'subtitle' => $module->subtitle,
                'type' => $module->type,
                'code' => $module->code,
                'icon' => $module->icon,
                'status' => (int) $module->status, // ahora siempre será 0 o 1
                'moduleOrder' => $module->moduleOrder,
                'link' => $module->link,
                'createdAt' => $module->created_at,
                'updatedAt' => $module->updated_at,
                'deletedAt' => $module->deleted_at,
                'parentModule' => $module->parentModule ? [
                    'id' => $module->parentModule->id,
                    'title' => $module->parentModule->title,
                    'code' => $module->parentModule->code,
                    'subtitle' => $module->parentModule->subtitle,
                ] : null,
            ];
        });

        return response()->json([
            'content' => $response,
            'totalElements' => $data->total(),
            'currentPage' => $data->currentPage() - 1, // Restamos 1 para ajustarlo al formato que pides
            'totalPages' => $data->lastPage(),
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
     * POST /module
     */
    public function store(Request $request)
    {
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

        $module = Module::create($validated);

        return response()->json($module);
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
}
