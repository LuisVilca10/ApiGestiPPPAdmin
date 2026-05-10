<?php

namespace App\Http\Resources\Module;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'code'        => $this->code,
            'subtitle'    => $this->subtitle,
            'type'        => $this->type,
            'icon'        => $this->icon,
            'status'      => $this->status,
            'moduleOrder' => $this->moduleOrder,
            'link'        => $this->link,
            'createdAt'   => $this->created_at?->toISOString(),
            'updatedAt'   => $this->updated_at?->toISOString(),
            'deletedAt'   => $this->deleted_at?->toISOString(),
        ];
    }
}
