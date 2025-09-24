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
        'user_id',
        'practice_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Una visita pertenece a un solo usuario
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class); // Una visita pertenece a una sola práctica
    }
}
