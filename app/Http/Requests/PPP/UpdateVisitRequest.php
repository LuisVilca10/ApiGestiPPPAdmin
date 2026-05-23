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
            'visit_status' => 'sometimes|string|in:Programada,Realizada,Cancelada',
        ];
    }

    public function messages(): array
    {
        return [
            'visit_type.in'   => 'El tipo de visita debe ser: Inicio, Medio o Final.',
            'visit_status.in' => 'El estado de visita debe ser: Programada, Realizada o Cancelada.',
            'visit_result.max'=> 'La calificación no puede superar 20.',
        ];
    }
}
