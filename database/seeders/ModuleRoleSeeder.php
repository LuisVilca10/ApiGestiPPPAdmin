<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ModuleRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole   = Role::where('name', 'Admin')->first();
        $studentRole = Role::where('name', 'Estudiante')->first();

        if (!$adminRole || !$studentRole) {
            $this->command->error('Roles Admin o Estudiante no encontrados. Ejecuta primero RoleAndPermissionsSeeder.');
            return;
        }

        // Admin → todos los módulos
        Module::all()->each(fn($m) => $m->roles()->syncWithoutDetaching([$adminRole->id]));

        // Estudiante → módulos de Prácticas y Seguimiento personal
        $codigosEstudiante = ['PR01', '01', '02', '03', 'AC01', '05'];

        Module::whereIn('code', $codigosEstudiante)
            ->get()
            ->each(fn($m) => $m->roles()->syncWithoutDetaching([$studentRole->id]));
    }
}
