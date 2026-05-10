<?php

namespace App\Http\Resources\Module;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'subtitle'    => $this->subtitle,
            'type'        => $this->type,
            'code'        => $this->code,
            'icon'        => $this->icon,
            'status'      => (int) $this->status,
            'moduleOrder' => $this->moduleOrder,
            'link'        => $this->link,
            'createdAt'   => $this->created_at?->toISOString(),
            'updatedAt'   => $this->updated_at?->toISOString(),
            'deletedAt'   => $this->deleted_at?->toISOString(),
            'parentModule' => $this->whenLoaded('parentModule', fn() => [
                'id'       => $this->parentModule->id,
                'title'    => $this->parentModule->title,
                'code'     => $this->parentModule->code,
                'subtitle' => $this->parentModule->subtitle,
            ]),
        ];
    }
}
