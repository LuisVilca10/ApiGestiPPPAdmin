<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Practice extends Model
{
    /** @use HasFactory<\Database\Factories\PracticeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',        // o name_empresa si prefieres español
        'ruc',
        'representative_name', // name_represent
        'representative_lastname', // lastname_represent
        'represent_title',     // trate_represent
        'represent_phone',     // phone_represent
        'student_activity',    // activity_student
        'hours_practice',      // hourse_practice -> corregir si lo quieres hours_practice
        'user_id',             // estudiante (FK -> users.id)
        'start_date',
        'end_date',
        'status',
    ];

    // Estudiante (usuario) dueño de la práctica
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Documentos de la práctica
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Visitas relacionadas
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}
