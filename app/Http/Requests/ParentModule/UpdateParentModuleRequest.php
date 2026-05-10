<?php

namespace App\Http\Requests\ParentModule;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateParentModuleRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => 'collapsable',
            'link' => '/example',
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title'       => ['required', 'string', 'max:100', Rule::unique('parent_modules', 'title')->ignore($id)],
            'subtitle'    => ['required', 'string', 'max:100', Rule::unique('parent_modules', 'subtitle')->ignore($id)],
            'code'        => ['nullable', 'string', Rule::unique('parent_modules', 'code')->ignore($id)],
            'status'      => 'required|boolean',
            'moduleOrder' => 'nullable|integer',
            'type'        => 'required|string|max:100',
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
