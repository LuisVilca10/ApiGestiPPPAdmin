<?php

namespace App\Http\Requests\Module;

use App\Http\Requests\ApiFormRequest;
use App\Models\Module;

class StoreModuleRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->has('parentModuleId')) {
            $merge['parent_module_id'] = $this->input('parentModuleId');
        }

        if (!$this->filled('moduleOrder')) {
            $parentId = $merge['parent_module_id'] ?? $this->input('parent_module_id');
            $merge['moduleOrder'] = (Module::where('parent_module_id', $parentId)->max('moduleOrder') ?? 0) + 1;
        }

        if (!$this->filled('icon')) {
            $merge['icon'] = 'circle';
        }

        $merge['type'] = 'basic';

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'title'            => 'required|string|max:100|unique:modules,title',
            'subtitle'         => 'required|string|max:100',
            'code'             => 'nullable|string|unique:modules,code',
            'status'           => 'required|boolean',
            'link'             => 'required|string|max:500',
            'parent_module_id' => 'required|exists:parent_modules,id',
            'type'             => 'required|string|max:100',
            'icon'             => 'nullable|string|max:100',
            'moduleOrder'      => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique' => 'Ya existe un módulo con ese título.',
            'code.unique'  => 'Ya existe un módulo con ese código.',
        ];
    }
}
