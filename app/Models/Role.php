<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];

    // Relación con módulos
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_role', 'role_id', 'module_id');
    }
}
