<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SunatService
{
    public function buscarRuc(string $ruc): ?array
    {
        $token = config('services.sunat.token');

        if (!$token) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get("https://dniruc.apisperu.com/api/v1/ruc/{$ruc}", [
                'token' => $token,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (empty($data['razonSocial'])) {
                return null;
            }

            return [
                'ruc'          => $data['ruc'] ?? $ruc,
                'name_empresa' => trim($data['razonSocial'] ?? '', '"'),
                'estado'       => $data['estado'] ?? null,
                'condicion'    => $data['condicion'] ?? null,
                'direccion'    => $data['direccion'] ?? null,
                'departamento' => $data['departamento'] ?? null,
                'provincia'    => $data['provincia'] ?? null,
                'distrito'     => $data['distrito'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::error('Error consultando SUNAT:', ['ruc' => $ruc, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function verificarActivo(string $ruc): array
    {
        $data = $this->buscarRuc($ruc);

        if ($data === null) {
            return ['valido' => false, 'mensaje' => "El RUC {$ruc} no fue encontrado en SUNAT. Verifique que sea correcto."];
        }

        if (($data['estado'] ?? '') !== 'ACTIVO') {
            return ['valido' => false, 'mensaje' => "La empresa con RUC {$ruc} no está ACTIVA en SUNAT (estado: {$data['estado']})."];
        }

        if (($data['condicion'] ?? '') !== 'HABIDO') {
            return ['valido' => false, 'mensaje' => "La empresa con RUC {$ruc} figura como NO HABIDO en SUNAT."];
        }

        return ['valido' => true, 'data' => $data];
    }
}
