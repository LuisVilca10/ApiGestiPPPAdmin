<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        $cycles = ['I','II','III','IV','V','VI','VII','VIII','IX','X'];

        return [
            'name'               => 'sometimes|string|max:255',
            'last_name'          => 'sometimes|string|max:255',
            'code'               => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('users', 'code')->ignore($id)],
            'username'           => ['sometimes', 'string', 'max:255', Rule::unique('users', 'username')->ignore($id)],
            'email'              => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password'           => 'sometimes|string|min:6',
            'role'               => 'sometimes|string|exists:roles,name',
            'academic_cycle'     => 'sometimes|string|in:' . implode(',', $cycles),
            'hours_of_practice'  => 'sometimes|integer|min:0',
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
