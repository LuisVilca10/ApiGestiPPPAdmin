<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class UpdatePracticeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'ruc'                 => ['sometimes', 'string', 'size:11', 'regex:/^(10|20)[0-9]{9}$/'],
            'name_empresa'        => 'sometimes|string|max:255',
            'name_represent'      => 'sometimes|string|max:255',
            'lastname_represent'  => 'sometimes|string|max:255',
            'trate_represent'     => 'sometimes|nullable|string|max:50',
            'phone_represent'     => 'sometimes|string|max:20',
            'activity_student'    => 'sometimes|string|max:500',
            'hourse_practice'     => 'sometimes|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'ruc.regex' => 'El RUC debe tener 11 dígitos y comenzar con 10 (persona natural) o 20 (persona jurídica).',
            'ruc.size'  => 'El RUC debe tener exactamente 11 dígitos.',
        ];
    }
}
