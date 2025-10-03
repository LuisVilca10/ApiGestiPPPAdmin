<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentModule extends Model
{
    /** @use HasFactory<\Database\Factories\ParentModuleFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'parent_modules';
    protected $fillable = [
        'title',
        'code',
        'subtitle',
        'type',
        'icon',
        'status',
        'moduleOrder',
        'link',
    ];

    protected $casts = [
        'moduleOrder' => 'integer',
    ];

    public function modules()
    {
        return $this->hasMany(Module::class, 'parent_module_id');
    }
}
