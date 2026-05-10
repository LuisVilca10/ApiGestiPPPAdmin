<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class StorePracticeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name_empresa'        => 'required|string|max:255',
            'ruc'                 => 'required|string|size:11|regex:/^[0-9]+$/',
            'name_represent'      => 'required|string|max:255',
            'lastname_represent'  => 'required|string|max:255',
            'trate_represent'     => 'nullable|string|max:50',
            'phone_represent'     => 'required|string|max:20',
            'activity_student'    => 'required|string|max:500',
            'hourse_practice'     => 'required|integer|min:1',
        ];
    }
}
