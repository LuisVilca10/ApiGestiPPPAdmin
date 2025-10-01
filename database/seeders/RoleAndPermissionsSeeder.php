<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use App\Models\Role; // tu modelo que extiende Spatie

class RoleAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permisos = [
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'editar_perfil',
            'formulario',
            'editar_roles'
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'api'],
            ['description' => 'Administrador con todos los permisos del sistema']
        );
        $admin->syncPermissions(Permission::all());

        $student = Role::firstOrCreate(
            ['name' => 'Estudiante', 'guard_name' => 'api'],
            ['description' => 'Estudiante habilitado para hacer prácticas']
        );
        $student->syncPermissions(['formulario']);
    }
}
