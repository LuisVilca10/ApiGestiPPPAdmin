<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Practice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PracticeService
{
    /**
     * Genera la Carta de Presentación en PDF, la guarda en Storage
     * y crea el Document asociado a la práctica.
     *
     * @return string URL pública del PDF generado
     */
    public function generateCartaPresentacion(Practice $practice): array
    {
        $estudiante = $practice->user;

        $data = [
            'estudiante'          => $estudiante,
            'empresa'             => $practice,
            'fecha_emision'       => now()->locale('es')->isoFormat('D [de] MMMM'),
            'destinatario_nombre' => 'Mg. Amed Vargas Martínez',
            'destinatario_titulo' => 'Director de la EP Administración',
            'numero_carta'        => 'CARTA N° ' . $practice->id . '-2025 /IS-FIA-UPEU-CJ',
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
            'document_name'   => 'Carta Presentacion - ' . $estudiante->name,
            'document_status' => 'En Proceso',
        ]);

        return [
            'url'  => Storage::url($filePath),
            'path' => Storage::disk('public')->path($filePath),
        ];
    }
}
