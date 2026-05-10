<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class UpdateDocumentRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'document_type' => 'sometimes|string',
            'document_path' => 'sometimes|string',
            'status'        => 'sometimes|string',
        ];
    }
}
