<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_type',
        'document_path',
        'original_name',
        'mime_type',
        'size',
        'document_status',
        'practice_id',
        'uploaded_by',
    ];

    // Relación con Practice
    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    // Usuario que subió el documento
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
