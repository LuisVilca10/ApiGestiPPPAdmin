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

    // Flujo oficial de documentos PPP en orden cronológico (6 hitos por evento)
    const TIPOS_PERMITIDOS = [
        'Carta Presentacion',      // 1 - Sistema auto-genera al aprobar la práctica
        'Carta Aceptacion',        // 2 - Estudiante (empresa acepta al alumno)
        'Plan de Practicas',       // 3 - Estudiante (define actividades y fechas; habilita visitas)
        'Ficha Visita',            // 4 - Docente (una por visita: Inicio, Medio, Final) vinculada a visit_id
        'Informe Jefe Empresa',    // 4d - Estudiante (empresa firma/sella, alumno sube)
        'Informe de Practicas',    // 5 - Estudiante (incluye autoevaluación)
        'Constancia de Practica',  // 6 - Estudiante (empresa certifica horas; cierra el evento)
        'Sustentacion de Practicas', // Hito final de carrera (fuera del ciclo por evento)
    ];

    // Solo el Docente puede subir
    const TIPOS_DOCENTE = [
        'Ficha Visita',
    ];

    // Solo Admin puede subir
    const TIPOS_ADMIN = [
        'Sustentacion de Practicas',
    ];

    // Tipos que el Estudiante puede subir
    const TIPOS_ESTUDIANTE = [
        'Carta Aceptacion',
        'Plan de Practicas',
        'Informe Jefe Empresa',
        'Informe de Practicas',
        'Constancia de Practica',
    ];

    protected $fillable = [
        'document_type',
        'document_name',
        'document_path',
        'document_status',
        'rejection_reason',
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
