<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;

class StorePracticeRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'ruc'                 => ['required', 'string', 'size:11', 'regex:/^(10|20)[0-9]{9}$/'],
            'name_empresa'        => 'required|string|max:255',
            'name_represent'      => 'required|string|max:255',
            'lastname_represent'  => 'required|string|max:255',
            'trate_represent'     => 'nullable|string|max:50',
            'phone_represent'     => 'required|string|max:20',
            'activity_student'    => 'required|string|max:500',
            'hourse_practice'     => 'required|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after:start_date',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            $user = auth('api')->user();
            $missing = [];

            if (empty($user->dni))    $missing[] = 'dni';
            if (empty($user->phone))  $missing[] = 'phone';
            if (empty($user->trato))  $missing[] = 'trato';
            if (empty($user->career)) $missing[] = 'career';

            if (!empty($missing)) {
                $v->errors()->add(
                    'perfil',
                    'Completa tu perfil antes de registrar una práctica. Campos requeridos: ' . implode(', ', $missing) . '.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'ruc.regex'          => 'El RUC debe tener 11 dígitos y comenzar con 10 (persona natural) o 20 (persona jurídica).',
            'ruc.size'           => 'El RUC debe tener exactamente 11 dígitos.',
            'end_date.after'     => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
