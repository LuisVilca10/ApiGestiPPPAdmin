<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

trait ValidatorTrait
{
    /**
     * Valida un request con reglas personalizadas
     *
     * @param Request $request
     * @param array $rules
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validateRequest(Request $request, array $rules)
    {
        return Validator::make($request->all(), $rules);
    }
}
