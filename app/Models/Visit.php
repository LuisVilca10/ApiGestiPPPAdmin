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
        'student_profile_id',
        'practice_id'
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }
    public function visitor()
    {
        return $this->belongsTo(User::class, 'visited_by');
    }
    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
}
