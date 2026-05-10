<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'last_name'          => $this->last_name,
            'code'               => $this->code,
            'username'           => $this->username,
            'email'              => $this->email,
            'photo_url'          => $this->photo_url,
            'academic_cycle'     => $this->academic_cycle,
            'hours_of_practice'  => $this->hours_of_practice,
            'email_verified'     => $this->hasVerifiedEmail(),
            'email_verified_at'  => $this->email_verified_at?->toISOString(),
            'roles'              => $this->getRoleNames(),
            'createdAt'          => $this->created_at?->toISOString(),
            'updatedAt'          => $this->updated_at?->toISOString(),
        ];
    }
}
