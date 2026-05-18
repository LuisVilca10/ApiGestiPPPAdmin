<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class ChangePasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'new_password' => 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        ];
    }
}
