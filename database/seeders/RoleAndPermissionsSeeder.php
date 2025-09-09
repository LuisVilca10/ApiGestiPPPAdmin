<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'editar_perfil',
            'formulario',
            'editar_roles'
        ];

        // Crear permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        // Crear roles y asignarles permisos
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'api'], // Asegúrate de usar 'api' aquí
            ['description' => 'Administrador con todos los permisos del sistema']
        );
        $admin->syncPermissions(Permission::all()); // Sincroniza todos los permisos con este rol

        $estudent = Role::firstOrCreate(
            ['name' => 'Estudiante', 'guard_name' => 'api'], // Asegúrate de usar 'api' aquí
            ['description' => 'Estudiante habilitado para hacer practicas']
        );
        $estudent->syncPermissions([
            'formulario',
        ]);
    }
}
