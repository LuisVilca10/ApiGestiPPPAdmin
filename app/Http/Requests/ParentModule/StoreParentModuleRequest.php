<?php

namespace App\Http\Requests\ParentModule;

use App\Http\Requests\ApiFormRequest;
use App\Models\ParentModule;

class StoreParentModuleRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => 'collapsable',
            'link' => '/example',
            'icon' => 'circle',
            'moduleOrder' => $this->filled('moduleOrder')
                ? $this->input('moduleOrder')
                : (ParentModule::max('moduleOrder') ?? 0) + 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'title'       => 'required|string|max:100|unique:parent_modules,title',
            'subtitle'    => 'required|string|max:100|unique:parent_modules,subtitle',
            'code'        => 'nullable|string|unique:parent_modules,code',
            'status'      => 'required|boolean',
            'type'        => 'required|string|max:100',
            'icon'        => 'required|string|max:100',
            'moduleOrder' => 'required|integer',
            'link'        => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'title.unique'    => 'Ya existe un módulo padre con ese título.',
            'subtitle.unique' => 'Ya existe un módulo padre con ese subtítulo.',
            'code.unique'     => 'Ya existe un módulo padre con ese código.',
        ];
    }
}
