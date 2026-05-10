<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    public function successResponse($data = [], string $message = 'Operación exitosa', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function errorResponse(string $message = 'Ocurrió un error', int $code = 400): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
        ], $code);
    }

    public function validationErrorResponse($errors, string $message = 'Errores de validación', int $code = 422): JsonResponse
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }


}
