<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class UpdateEmpresaRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'ruc'                => ['sometimes', 'string', 'size:11', "unique:empresas,ruc,{$id}", 'regex:/^(10|20)[0-9]{9}$/'],
            'name_empresa'       => 'sometimes|string|max:255',
            'name_represent'     => 'sometimes|string|max:255',
            'lastname_represent' => 'sometimes|string|max:255',
            'trate_represent'    => 'nullable|string|max:50',
            'phone_represent'    => 'sometimes|string|max:20',
            'departamento'       => 'nullable|string|max:100',
            'provincia'          => 'nullable|string|max:100',
            'distrito'           => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'ruc.regex'  => 'El RUC debe tener 11 dígitos y comenzar con 10 (natural) o 20 (jurídica).',
            'ruc.size'   => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.unique' => 'Ya existe otra empresa registrada con ese RUC.',
        ];
    }
}
