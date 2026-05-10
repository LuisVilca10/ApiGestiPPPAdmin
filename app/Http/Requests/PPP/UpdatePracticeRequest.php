<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class UpdatePracticeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name_empresa'        => 'sometimes|string|max:255',
            'ruc'                 => 'sometimes|string|size:11|regex:/^[0-9]+$/',
            'name_represent'      => 'sometimes|string|max:255',
            'lastname_represent'  => 'sometimes|string|max:255',
            'trate_represent'     => 'sometimes|nullable|string|max:50',
            'phone_represent'     => 'sometimes|string|max:20',
            'activity_student'    => 'sometimes|string|max:500',
            'hourse_practice'     => 'sometimes|integer|min:1',
        ];
    }
}
