<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    // Define la relación con módulos
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_role', 'role_id', 'module_id');
    }
}
