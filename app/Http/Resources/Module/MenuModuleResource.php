<?php

namespace App\Http\Resources\Module;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Versión reducida de Module para el árbol de menú (sin parentModule).
 */
class MenuModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'subtitle'    => $this->subtitle,
            'type'        => $this->type,
            'icon'        => $this->icon,
            'link'        => $this->link,
            'moduleOrder' => $this->moduleOrder,
            'createdAt'   => $this->created_at?->toISOString(),
            'updatedAt'   => $this->updated_at?->toISOString(),
            'deletedAt'   => $this->deleted_at?->toISOString(),
        ];
    }
}
