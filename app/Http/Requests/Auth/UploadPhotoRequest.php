<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class UploadPhotoRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'photo' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
