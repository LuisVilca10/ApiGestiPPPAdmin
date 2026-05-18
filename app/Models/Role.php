<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_role', 'role_id', 'module_id');
    }
}
