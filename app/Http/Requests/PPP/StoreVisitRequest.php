<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class StoreVisitRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'practice_id'  => 'required|exists:practices,id',
            'visit_date'   => 'required|date',
            'visit_type'   => 'required|string|in:Inicio,Medio,Final',
            'visit_notes'  => 'nullable|string',
            'visit_result' => 'nullable|numeric|min:0|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'visit_type.in'      => 'El tipo de visita debe ser: Inicio, Medio o Final.',
            'visit_result.max'   => 'La calificación no puede superar 20.',
            'practice_id.exists' => 'La práctica indicada no existe.',
        ];
    }
}
