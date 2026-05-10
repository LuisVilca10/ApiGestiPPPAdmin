<?php

namespace App\Http\Requests\Module;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateModuleRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('parentModuleId')) {
            $this->merge(['parent_module_id' => $this->input('parentModuleId')]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title'            => ['required', 'string', 'max:100', Rule::unique('modules', 'title')->ignore($id)],
            'subtitle'         => 'required|string|max:100',
            'code'             => ['nullable', 'string', Rule::unique('modules', 'code')->ignore($id)],
            'icon'             => 'nullable|string|max:100',
            'status'           => 'required|boolean',
            'moduleOrder'      => 'nullable|integer',
            'link'             => 'required|string|max:500',
            'parent_module_id' => 'required|exists:parent_modules,id',
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
