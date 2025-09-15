<?php

namespace App\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

trait RolePermissions
{
    // Asignar rol a un usuario
    public function assignRoleToUser($user, $role)
    {
        $role = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        $user->assignRole($role);
    }

    // Asignar permisos a un rol
    public function assignPermissionsToRole($role, $permissions)
    {
        $role = Role::firstOrCreate(['name' => $role, 'guard_name' => 'api']);
        $role->givePermissionTo($permissions);
    }

    // Verificar si el usuario tiene un permiso
    public function checkPermission($user, $permission)
    {
        return $user->hasPermissionTo($permission);
    }
}
