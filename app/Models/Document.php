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
        'document_name',
        'document_path',
        'document_status',
        'practice_id'
    ];
    public function practice()
    {
        return $this->belongsTo(Practice::class); // Un documento pertenece a una sola práctica
    }
}
