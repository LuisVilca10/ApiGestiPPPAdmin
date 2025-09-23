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
        'name_empresa',
        'ruc',
        'name_represent',
        'lastname_represent',
        'trate_represent',
        'phone_represent',
        'activity_student',
        'hourse_practice',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class); // Una práctica pertenece a un solo usuario
    }
    public function documents()
    {
        return $this->hasMany(Document::class); // Una práctica puede tener muchos documentos
    }
    public function visits()
    {
        return $this->hasMany(Visit::class); // Una práctica puede tener muchas visitas
    }
}
