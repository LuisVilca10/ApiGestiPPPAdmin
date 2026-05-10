<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\ApiFormRequest;

class AssignModulesToRoleRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'modules'   => 'required|array',
            'modules.*' => 'integer|exists:modules,id',
        ];
    }
}
