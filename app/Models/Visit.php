<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    /** @use HasFactory<\Database\Factories\VisitFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_date',
        'visit_type',
        'visit_notes',
        'visit_result',
        'user_id',      // estudiante (FK -> users.id)
        'practice_id',  // nullable
        'visited_by',   // quien hizo la visita (FK -> users.id)
    ];

    // Estudiante (usuario) que fue visitado
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Usuario que realizó la visita
    public function visitor()
    {
        return $this->belongsTo(User::class, 'visited_by');
    }

    // Práctica opcional asociada
    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}
