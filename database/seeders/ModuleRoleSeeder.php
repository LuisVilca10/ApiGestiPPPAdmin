<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ModuleRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::whereIn('name', [
            'Admin',
            'Encargado de Practicas',
            'Coordinador',
            'Docente',
            'Estudiante',
        ])->get()->keyBy('name');

        if ($roles->count() < 5) {
            $this->command->error('No se encontraron todos los roles. Ejecuta primero RoleAndPermissionsSeeder.');
            return;
        }

        /*
         * Matriz de módulos por rol
         *
         * Código  | Módulo                    | Admin | Encargado | Coordinador | Docente | Estudiante
         * --------|---------------------------|-------|-----------|-------------|---------|----------
         * PR01    | Dashboard de Prácticas    |  ✅   |    ✅     |     ✅      |   ✅    |    ✅
         * 01      | Gestión / Inscripción     |  ✅   |    ✅     |     ✅      |         |    ✅
         * 02      | Cartas de Presentación    |  ✅   |    ✅     |             |         |    ✅
         * AC01    | Cartas de Aceptación      |  ✅   |    ✅     |             |         |    ✅
         * 03      | Bitácora de Documentos    |  ✅   |    ✅     |             |   ✅    |    ✅
         * INF-01  | Informe de Prácticas      |  ✅   |    ✅     |             |         |    ✅
         * 04      | Reportes de Desempeño     |  ✅   |    ✅     |     ✅      |         |
         * 05      | Evaluaciones              |  ✅   |    ✅     |     ✅      |   ✅    |    ✅
         * 06      | Validación de Documentos  |  ✅   |    ✅     |             |         |
         * VIS-01  | Visitas de Supervisión    |  ✅   |    ✅     |     ✅      |   ✅    |    ✅
         * 07      | Roles                     |  ✅   |           |             |         |
         * 08      | Módulos Padres            |  ✅   |           |             |         |
         * 09      | Módulos                   |  ✅   |           |             |         |
         * USR-01  | Usuarios                  |  ✅   |           |             |         |
         * EMP-01  | Empresas                  |  ✅   |    ✅     |             |         |
         */

        $map = [
            'Admin' => [
                'PR01', '01', '02', 'AC01', '03', 'INF-01',
                '04', '05', '06', 'VIS-01',
                '07', '08', '09', 'USR-01', 'EMP-01',
            ],
            'Encargado de Practicas' => [
                'PR01', '01', '02', 'AC01', '03', 'INF-01',
                '04', '05', '06', 'VIS-01', 'EMP-01',
            ],
            'Coordinador' => [
                'PR01', '01', '04', '05', 'VIS-01',
            ],
            'Docente' => [
                'PR01', '03', '05', 'VIS-01',
            ],
            'Estudiante' => [
                'PR01', '01', '02', 'AC01', '03', 'INF-01', '05', 'VIS-01',
            ],
        ];

        foreach ($map as $roleName => $codigos) {
            $role = $roles->get($roleName);
            if (!$role) continue;

            Module::whereIn('code', $codigos)
                ->get()
                ->each(fn($m) => $m->roles()->syncWithoutDetaching([$role->id]));
        }
    }
}
