<?php

namespace App\Http\Controllers\Api\Modules;

use App\Models\ParentModule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ParentModuleController
{
    /**
     * GET /parent-module?page=&size=&name=
     */

    public function listPaginate(Request $request)
    {
        $size = $request->input('size', 10);
        $name = $request->input('name');

        $query = ParentModule::query();

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

        $data = $query->paginate($size);
        // Construir la respuesta con el formato solicitado
        $response = [
            'totalPages' => $data->lastPage(),
            'currentPage' => $data->currentPage() - 1,
            'totalElements' => $data->total(),
            'content' => $data->map(function ($module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'code' => $module->code,
                    'subtitle' => $module->subtitle,
                    'type' => $module->type,
                    'icon' => $module->icon,
                    'status' => $module->status,
                    'moduleOrder' => $module->moduleOrder,
                    'link' => $module->link,
                    'created_at' => Carbon::parse($module->created_at)->toISOString(), // Convierte la fecha a Carbon
                    'updated_at' => Carbon::parse($module->updated_at)->toISOString(), // Convierte la fecha a Carbon
                    'deleted_at' => $module->deleted_at ? Carbon::parse($module->deleted_at)->toISOString() : null, // Convierte la fecha a Carbon si existe
                ];
            }),

        ];

        return response()->json($response);
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

        $parentModules = $query->get();

        $response = $parentModules->map(function ($module) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'code' => $module->code,
                'subtitle' => $module->subtitle,
                'type' => $module->type,
                'icon' => $module->icon,
                'status' => $module->status,  // Aquí ya está convertido a booleano
                'moduleOrder' => $module->moduleOrder,
                'link' => $module->link,
                'created_at' => $module->createdAt,  // Usando el accesor
                'updated_at' => $module->updatedAt,  // Usando el accesor
                'deleted_at' => $module->deletedAt,  // Usando el accesor
            ];
        });
        return response()->json($response);
    }

    public function listar(Request $request)
    {
        $name = $request->input('name');
        $query = ParentModule::query();

        if ($name) {
            $query->where('title', 'like', "%$name%");
        }

        $parentModules = $query->get();

        $response = $parentModules->map(function ($module) {
            return [
                'id' => $module->id,
                'title' => $module->title,
                'code' => $module->code,
                'subtitle' => $module->subtitle,
                'type' => $module->type,
                'icon' => $module->icon,
                'status' => $module->status,  // Aquí ya está convertido a booleano
                'moduleOrder' => $module->moduleOrder,
                'link' => $module->link,
                'createdAt' => $module->createdAt,  // Usando el accesor
                'updatedAt' => $module->updatedAt,  // Usando el accesor
                'deletedAt' => $module->deletedAt,  // Usando el accesor
            ];
        });

        return response()->json($response);
    }

    /**
     * GET /parent-module/list-detail-module-list
     */
    public function listDetailModuleList()
    {
        $parents = ParentModule::with('modules')->get();
        return response()->json($parents);
    }

    /**
     * POST /parent-module
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:100',
            'code'     => 'required|string|max:10|unique:parent_modules,code',
            'subtitle' => 'required|string|max:100',
            'status'   => 'required|integer',
        ], [
            'code.unique' => 'El código ingresado ya existe en el sistema.',
            'code.required' => 'El campo código es obligatorio.',
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
     * GET /parent-module/{id}
     */
    public function show($id)
    {
        $module = ParentModule::findOrFail($id);
        return response()->json($module);
    }

    /**
     * PUT /parent-module/{id}
     */
    public function update(Request $request, $id)
    {
        $module = ParentModule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:100',
            'code'        => ['nullable', 'string', 'max:10', Rule::unique('parent_modules', 'code')->ignore($id)],
            'subtitle'    => 'required|string|max:100',
            'type'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:100',
            'status'      => 'required|integer|in:0,1',   // <- era boolean, mejor int 0/1 para tu caso
            'moduleOrder' => 'required|integer',
            'link'        => 'required|string|max:500',
        ], [
            'code.unique'   => 'El código ingresado ya existe en el sistema.',
            'code.max'      => 'El código no debe exceder 10 caracteres.',
            'status.in'     => 'El estado debe ser 0 (inactivo) o 1 (activo).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Actualiza y devuelve el registro refrescado
        $module->update($validated);

        return response()->json($module->refresh());
    }


    /**
     * DELETE /parent-module/{id}
     */
    public function destroy($id)
    {
        $module = ParentModule::findOrFail($id);
        $module->delete();

        // Retornar la paginación por defecto como en Java
        $data = ParentModule::paginate(20);

        return response()->json($data);
    }
}
