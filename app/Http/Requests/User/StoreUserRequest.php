<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

class StoreUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $cycles = ['I','II','III','IV','V','VI','VII','VIII','IX','X'];

        return [
            'name'               => 'required|string|max:255',
            'last_name'          => 'required|string|max:255',
            'code'               => 'nullable|string|max:255|unique:users,code',
            'username'           => 'required|string|max:255|unique:users,username',
            'email'              => 'nullable|email|max:255|unique:users,email',
            'password'           => 'required|string|min:6',
            'role'               => 'nullable|string|exists:roles,name',
            'academic_cycle'     => 'sometimes|string|in:' . implode(',', $cycles),
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'email.unique'    => 'El correo electrónico ya está registrado.',
            'code.unique'     => 'El código ya está en uso.',
        ];
    }
}
