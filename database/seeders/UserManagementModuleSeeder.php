<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ParentModule;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserManagementModuleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole      = Role::where('name', 'Admin')->first();
        $estudianteRole = Role::where('name', 'Estudiante')->first();

        // ── Parent: Configuración (ya existe, code 03) ──
        $parentConfig = ParentModule::where('code', '03')->first();

        if ($parentConfig) {
            $moduloUsuarios = Module::firstOrCreate(
                ['code' => 'USR-01'],
                [
                    'title'            => 'Usuarios',
                    'subtitle'         => 'Gestión de usuarios del sistema',
                    'type'             => 'basic',
                    'icon'             => 'heroicons_outline:users',
                    'status'           => 1,
                    'moduleOrder'      => Module::where('parent_module_id', $parentConfig->id)->max('moduleOrder') + 1,
                    'link'             => '/homeScreen/setup/users',
                    'parent_module_id' => $parentConfig->id,
                ]
            );

            $moduloUsuarios->roles()->syncWithoutDetaching([$adminRole->id]);
        }

    }
}
