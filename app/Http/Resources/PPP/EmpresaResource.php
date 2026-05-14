<?php

namespace App\Http\Resources\PPP;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'ruc'                => $this->ruc,
            'name_empresa'       => $this->name_empresa,
            'name_represent'     => $this->name_represent,
            'lastname_represent' => $this->lastname_represent,
            'trate_represent'    => $this->trate_represent,
            'phone_represent'    => $this->phone_represent,
            'departamento'       => $this->departamento,
            'provincia'          => $this->provincia,
            'distrito'           => $this->distrito,
            'practices_count'    => $this->when(isset($this->practices_count), $this->practices_count),
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),
        ];
    }
}
