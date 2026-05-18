<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;
use App\Models\Document;

class UpdateDocumentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $types    = implode(',', Document::TIPOS_PERMITIDOS);
        $statuses = 'En Proceso,Aprobado,Rechazado';

        return [
            'document_type'   => "sometimes|string|in:{$types}",
            'document_status' => "sometimes|string|in:{$statuses}",
        ];
    }

    public function messages(): array
    {
        $types = implode(', ', Document::TIPOS_PERMITIDOS);
        return [
            'document_type.in'   => "Tipo de documento no válido. Opciones: {$types}",
            'document_status.in' => 'Estado no válido. Opciones: En Proceso, Aprobado, Rechazado',
        ];
    }
}
