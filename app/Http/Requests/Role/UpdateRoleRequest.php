<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\ApiFormRequest;

class UpdateRoleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->route('id'),
        ];
    }
}
