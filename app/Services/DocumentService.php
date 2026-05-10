<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class DocumentService
{
    /**
     * Almacena un archivo subido y crea el registro en la base de datos.
     */
    public function upload(string $practiceId, string $documentType, UploadedFile $file): Document
    {
        $username      = Auth::user()->code;
        $extension     = $file->getClientOriginalExtension();
        $archivoNombre = $documentType . ' - ' . $username . ' - ' . now()->format('dmYHi') . '.' . $extension;

        $path = $file->storeAs('practicas', $archivoNombre, 'public');

        return Document::create([
            'practice_id'     => $practiceId,
            'document_type'   => $documentType,
            'document_name'   => $archivoNombre,
            'document_path'   => $path,
            'document_status' => 'En Proceso',
        ]);
    }
}
