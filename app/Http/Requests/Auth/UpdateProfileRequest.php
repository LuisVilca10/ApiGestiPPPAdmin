<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class UpdateProfileRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $userId = auth('api')->id();

        $rules = [
            'name'          => 'sometimes|string|max:255',
            'last_name'     => 'sometimes|string|max:255',
            'code'          => 'sometimes|nullable|string|max:255',
            'username'      => 'sometimes|string|max:255|unique:users,username,' . $userId,
            'email'         => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'photo_url'     => 'sometimes|nullable|string|max:500',
            'dni'           => 'sometimes|nullable|string|max:20',
            'phone'         => 'sometimes|nullable|string|max:20',
            'career'        => 'sometimes|nullable|string|max:255',
            'academic_cycle'=> 'sometimes|nullable|string|max:20',
        ];

        if ($this->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password']     = 'required|string|min:6|max:255';
            $rules['confirm_password'] = 'required|string|same:new_password';
        }

        return $rules;
    }
}
