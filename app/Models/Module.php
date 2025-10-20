<?php

// App\Models\Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
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

    protected $casts = [
        'moduleOrder' => 'integer',
        'status'      => 'integer',
    ];
    /**
     * Relación con roles (muchos a muchos)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'module_role', 'module_id', 'role_id');
    }

    public function parentModule()
    {
        return $this->belongsTo(ParentModule::class, 'parent_module_id');
    }
}
