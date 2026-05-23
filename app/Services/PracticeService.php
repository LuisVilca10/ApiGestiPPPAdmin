<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Practice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PracticeService
{
    public function generateCartaPresentacion(Practice $practice): array
    {
        $estudiante = $practice->user;
        $year       = now()->year;

        $data = [
            'estudiante'          => $estudiante,
            'empresa'             => $practice->empresa,
            'practica'            => $practice,
            'fecha_emision'       => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'destinatario_nombre' => 'Mg. Amed Vargas Martínez',
            'destinatario_titulo' => 'Director de la EP Administración',
            'numero_carta'        => "CARTA N° {$practice->id}-{$year}/EP.ADM-FCA-UPEU-CJ",
            'fecha_inicio'        => $practice->start_date
                ? Carbon::parse($practice->start_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                : null,
            'fecha_fin'           => $practice->end_date
                ? Carbon::parse($practice->end_date)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
                : null,
        ];

        $pdf      = Pdf::loadView('pdfs.carta_presentacion', $data);
        $fecha    = Carbon::now()->format('dmYHi');
        $fileName = 'CARTA_PRESENTACION_' . $estudiante->code . '_' . $fecha . '.pdf';
        $filePath = 'practicas/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        Document::create([
            'practice_id'     => $practice->id,
            'document_type'   => 'Carta Presentacion',
            'document_path'   => $filePath,
            'document_name'   => 'Carta Presentacion - ' . $estudiante->name . ' ' . $estudiante->last_name,
            'document_status' => 'Aprobado',
        ]);

        return [
            'url'  => url(Storage::url($filePath)),
            'path' => Storage::disk('public')->path($filePath),
        ];
    }

    /**
     * Sugiere fechas para las 3 visitas (Inicio, Medio, Final)
     * distribuidas uniformemente entre start_date y end_date.
     */
    public function sugerirFechasVisitas(Practice $practice): array
    {
        if (!$practice->start_date || !$practice->end_date) {
            return [];
        }

        $inicio = Carbon::parse($practice->start_date);
        $fin    = Carbon::parse($practice->end_date);
        $dias   = $inicio->diffInDays($fin);

        return [
            'Inicio' => $inicio->copy()->addDays((int) ($dias * 0.15))->toDateString(),
            'Medio'  => $inicio->copy()->addDays((int) ($dias * 0.50))->toDateString(),
            'Final'  => $inicio->copy()->addDays((int) ($dias * 0.85))->toDateString(),
        ];
    }
}
