<?php

namespace App\Http\Controllers\Api\Modules;

use App\Models\ParentModule;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        if ($name) {
            $query->where('title', 'like', "%$name%");
        }

        $data = $query->paginate($size);

        // Construir la respuesta con el formato solicitado
        $response = [
            'totalPages' => $data->lastPage(),
            'currentPage' => $data->currentPage() - 1, // Restamos 1 para ajustarlo al formato que pides
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
                    'createdAt' => Carbon::parse($module->created_at)->toISOString(), // Convierte la fecha a Carbon
                    'updatedAt' => Carbon::parse($module->updated_at)->toISOString(), // Convierte la fecha a Carbon
                    'deletedAt' => $module->deleted_at ? Carbon::parse($module->deleted_at)->toISOString() : null, // Convierte la fecha a Carbon si existe
                ];
            }),
            'totalElements' => $data->total(),
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
                'createdAt' => $module->createdAt,  // Usando el accesor
                'updatedAt' => $module->updatedAt,  // Usando el accesor
                'deletedAt' => $module->deletedAt,  // Usando el accesor
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
            'title' => 'required|string|max:100',
            'code' => 'nullable|string',
            'subtitle' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            'moduleOrder' => 'required|integer',
            'link' => 'required|string|max:500',
        ]);

        $module = ParentModule::create($validated);
        return response()->json($module);
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

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'code' => 'nullable|string',
            'subtitle' => 'required|string|max:100',
            'type' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'status' => 'required|boolean',
            'moduleOrder' => 'required|integer',
            'link' => 'required|string|max:500',
        ]);

        $module->update($validated);

        return response()->json($module);
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
