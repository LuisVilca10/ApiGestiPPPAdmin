<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Visit;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    // Flujo oficial de documentos PPP en orden cronológico
    const TIPOS_PERMITIDOS = [
        'Carta Presentacion',          // 1 - Auto-generada al aprobar la práctica
        'Carta Aceptacion',            // 2 - Estudiante sube (empresa acepta)
        'Plan de Practicas',           // 3 - Estudiante sube su plan
        'Monitoreo y Evaluacion',      // 4 - Docente de la EP sube evaluación de seguimiento
        'Evaluacion Jefe Inmediato',   // 5 - Admin sube evaluación del jefe de empresa
        'Informe de Practicas',        // 6 - Estudiante sube (requiere 1500h completadas)
        'Sustentacion de Practicas',   // 7 - Admin/Docente registra resultado de sustentación
    ];

    // Tipos que solo el admin/docente puede subir
    const TIPOS_ADMIN = [
        'Monitoreo y Evaluacion',
        'Evaluacion Jefe Inmediato',
        'Sustentacion de Practicas',
    ];

    // Tipos que el estudiante puede subir
    const TIPOS_ESTUDIANTE = [
        'Carta Aceptacion',
        'Plan de Practicas',
        'Informe de Practicas',
    ];

    protected $fillable = [
        'document_type',
        'document_name',
        'document_path',
        'document_status',
        'practice_id',
        'visit_id',
    ];

    // Siempre devuelve la URL pública completa
    protected function documentPath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Storage::url($value) : null,
        );
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}
