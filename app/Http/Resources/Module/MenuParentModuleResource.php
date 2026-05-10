<?php

namespace App\Http\Resources\Module;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Module\MenuModuleResource;

/**
 * Versión reducida de ParentModule usada en el menú de navegación,
 * con sus hijos (modules) anidados.
 */
class MenuParentModuleResource extends JsonResource
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
            'children'    => MenuModuleResource::collection($this->whenLoaded('modules')),
        ];
    }
}
