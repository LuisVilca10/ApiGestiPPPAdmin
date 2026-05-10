<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class UpdateVisitRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'visit_date'   => 'sometimes|date',
            'visit_type'   => 'sometimes|string|in:Inicio,Medio,Final',
            'visit_notes'  => 'sometimes|string',
            'visit_result' => 'sometimes|numeric|min:0|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'visit_type.in'    => 'El tipo de visita debe ser: Inicio, Medio o Final.',
            'visit_result.max' => 'La calificación no puede superar 20.',
        ];
    }
}
