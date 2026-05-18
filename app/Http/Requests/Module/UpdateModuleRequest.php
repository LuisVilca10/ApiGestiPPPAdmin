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
            'title'            => ['sometimes', 'string', 'max:100', Rule::unique('modules', 'title')->ignore($id)],
            'subtitle'         => 'sometimes|string|max:100',
            'code'             => ['sometimes', 'nullable', 'string', Rule::unique('modules', 'code')->ignore($id)],
            'icon'             => 'sometimes|nullable|string|max:100',
            'status'           => 'sometimes|boolean',
            'moduleOrder'      => 'sometimes|nullable|integer',
            'link'             => 'sometimes|string|max:500',
            'parent_module_id' => 'sometimes|exists:parent_modules,id',
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
