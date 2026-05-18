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

        // Estudiante → Inicio completo + Documentos completo + Evaluaciones
        $codigosEstudiante = [
            'PR01',  // Dashboard de Prácticas  (Inicio)
            '01',    // Inscripción a Prácticas  (Inicio)
            '02',    // Cartas de Presentación   (Docs)
            'AC01',  // Cartas de Aceptación     (Docs)
            '03',    // Bitácora de Documentos   (Docs)
            'INF-01', // Informe de Prácticas    (Docs)
            '05',    // Evaluaciones             (Seguimiento)
        ];

        Module::whereIn('code', $codigosEstudiante)
            ->get()
            ->each(fn($m) => $m->roles()->syncWithoutDetaching([$studentRole->id]));
    }
}
