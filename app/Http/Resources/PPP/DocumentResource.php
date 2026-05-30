<?php

namespace App\Http\Resources\PPP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'document_type'    => $this->document_type,
            'document_name'    => $this->document_name,
            'document_path'    => $this->document_path,
            'document_status'  => $this->document_status,
            'rejection_reason' => $this->rejection_reason,
            'practice_id'      => $this->practice_id,
            'visit_id'         => $this->visit_id,
            'practice_name'    => $this->whenLoaded('practice', fn() => $this->practice->empresa?->name_empresa ?? 'Sin práctica'),
            'created_at'       => $this->created_at?->toISOString(),
        ];
    }
}
