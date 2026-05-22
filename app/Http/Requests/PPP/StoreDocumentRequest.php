<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;
use App\Models\Document;

class StoreDocumentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $types = implode(',', Document::TIPOS_PERMITIDOS);

        return [
            'practice_id'   => 'required|exists:practices,id',
            'document_type' => "required|string|in:{$types}",
            'file'          => 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:30720',
            'visit_id'      => 'nullable|exists:visits,id',
        ];
    }

    public function messages(): array
    {
        $types = implode(', ', Document::TIPOS_PERMITIDOS);
        return [
            'document_type.in' => "El tipo de documento no es válido. Tipos permitidos: {$types}",
        ];
    }
}
