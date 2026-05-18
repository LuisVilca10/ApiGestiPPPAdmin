<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->route('id'))],
        ];
    }
}
