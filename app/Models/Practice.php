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
        'student_profile_id',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function visits()
    {
        return $this->hasMany(Visit::class);
    } // si las atas a práctica

}
