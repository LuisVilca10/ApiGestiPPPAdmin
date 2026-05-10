<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class RegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'code'              => 'nullable|string|max:255|unique:users,code',
            'username'          => 'required|string|max:255|unique:users,username',
            'email'             => 'required|string|email|max:255|unique:users,email',
            'password'          => 'required|string|min:6|max:255',
            'verification_code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique'            => 'El nombre de usuario ya está en uso.',
            'email.unique'               => 'Este correo ya está registrado.',
            'email.required'             => 'El correo es obligatorio para verificar tu cuenta.',
            'password.min'               => 'La contraseña debe tener al menos 6 caracteres.',
            'verification_code.required' => 'El código de verificación es obligatorio.',
            'verification_code.size'     => 'El código de verificación debe tener 6 dígitos.',
        ];
    }
}
