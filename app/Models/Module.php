<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory, SoftDeletes;
    protected $table = 'modules';
    protected $fillable = [
        'title',
        'subtitle',
        'type',
        'code',
        'icon',
        'status',
        'moduleOrder',
        'link',
        'parent_module_id',
    ];
    public function parentModule()
    {
        return $this->belongsTo(ParentModule::class, 'parent_module_id');
    }
}
