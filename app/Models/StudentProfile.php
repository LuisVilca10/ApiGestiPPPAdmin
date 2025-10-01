<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'program',
        'semester',
        'practice_hours',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function practices()
    {
        return $this->hasMany(Practice::class);
    }
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
    public function documents()
    {
        // documentos a través de prácticas
        return $this->hasManyThrough(Document::class, Practice::class);
    }
}
