<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\ApiFormRequest;

class AssignRoleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|string|exists:roles,name',
        ];
    }
}
