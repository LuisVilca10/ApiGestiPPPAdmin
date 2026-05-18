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
            'title'       => ['sometimes', 'string', 'max:100', Rule::unique('parent_modules', 'title')->ignore($id)],
            'subtitle'    => ['sometimes', 'string', 'max:100', Rule::unique('parent_modules', 'subtitle')->ignore($id)],
            'code'        => ['sometimes', 'nullable', 'string', Rule::unique('parent_modules', 'code')->ignore($id)],
            'status'      => 'sometimes|boolean',
            'moduleOrder' => 'sometimes|nullable|integer',
            'type'        => 'sometimes|string|max:100',
            'link'        => 'sometimes|string|max:500',
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
