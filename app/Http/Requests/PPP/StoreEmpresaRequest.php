<?php

namespace App\Http\Requests\PPP;

use App\Http\Requests\ApiFormRequest;

class StoreEmpresaRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'ruc'                => ['required', 'string', 'size:11', 'unique:empresas,ruc', 'regex:/^(10|20)[0-9]{9}$/'],
            'name_empresa'       => 'required|string|max:255',
            'name_represent'     => 'required|string|max:255',
            'lastname_represent' => 'required|string|max:255',
            'trate_represent'    => 'nullable|string|max:50',
            'phone_represent'    => 'required|string|max:20',
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
            'ruc.unique' => 'Ya existe una empresa registrada con ese RUC.',
        ];
    }
}
