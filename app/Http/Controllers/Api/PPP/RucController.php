<?php

namespace App\Http\Controllers\Api\PPP;

use App\Models\Empresa;
use App\Services\SunatService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class RucController
{
    use ApiResponseTrait;

    public function __construct(private readonly SunatService $sunatService) {}

    public function lookup(string $ruc): JsonResponse
    {
        if (!preg_match('/^(10|20)[0-9]{9}$/', $ruc)) {
            return $this->errorResponse('RUC inválido. Debe tener 11 dígitos y comenzar con 10 o 20.', 422);
        }

        // Buscar en tabla empresas primero
        $empresa = Empresa::where('ruc', $ruc)->first();
        if ($empresa) {
            return $this->successResponse(
                array_merge($empresa->toArray(), ['source' => 'internal']),
                'Empresa encontrada en registros internos.'
            );
        }

        // Consultar SUNAT
        $token = config('services.sunat.token');
        if (!$token) {
            return $this->errorResponse('No hay token SUNAT configurado y el RUC no existe en registros internos.', 404);
        }

        $data = $this->sunatService->buscarRuc($ruc);

        if (!$data) {
            return $this->errorResponse('RUC no encontrado en SUNAT.', 404);
        }

        return $this->successResponse([
            'ruc'                => $data['ruc'],
            'name_empresa'       => $data['name_empresa'],
            'estado'             => $data['estado'],
            'condicion'          => $data['condicion'],
            'direccion'          => $data['direccion'],
            'departamento'       => $data['departamento'],
            'provincia'          => $data['provincia'],
            'distrito'           => $data['distrito'],
            // SUNAT no entrega datos del representante
            'name_represent'     => null,
            'lastname_represent' => null,
            'trate_represent'    => null,
            'phone_represent'    => null,
            'source'             => 'sunat',
        ], 'Empresa encontrada en SUNAT. Complete los datos del representante.');
    }
}
