<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [

            // ── 1. INICIO DE PRÁCTICAS (parent_module_id: 1) ──────────────
            [
                'title'            => 'Dashboard de Prácticas',
                'code'             => 'PR01',
                'subtitle'         => 'Dashboard de prácticas preprofesionales',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:home',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/inicio/dashboard',
                'parent_module_id' => 1,
            ],
            [
                'title'            => 'Inscripción a Prácticas',
                'code'             => '01',
                'subtitle'         => 'Registro de inscripciones a prácticas preprofesionales',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-document-list',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/inicio/inscripcion-practicas',
                'parent_module_id' => 1,
            ],

            // ── 2. DOCUMENTOS DE PRÁCTICAS (parent_module_id: 2) ──────────
            [
                'title'            => 'Cartas de Presentación',
                'code'             => '02',
                'subtitle'         => 'Gestión de cartas de presentación para prácticas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:envelope-open',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/docs/cartas-presentacion',
                'parent_module_id' => 2,
            ],
            [
                'title'            => 'Cartas de Aceptación',
                'code'             => 'AC01',
                'subtitle'         => 'Gestión de cartas de aceptación de empresa',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:check-badge',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/docs/cartas-aceptacion',
                'parent_module_id' => 2,
            ],
            [
                'title'            => 'Bitácora de Documentos',
                'code'             => '03',
                'subtitle'         => 'Historial y estado de mis documentos',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:folder-open',
                'status'           => 1,
                'moduleOrder'      => 3,
                'link'             => '/homeScreen/docs/bitacora',
                'parent_module_id' => 2,
            ],
            [
                'title'            => 'Informe de Prácticas',
                'code'             => 'INF-01',
                'subtitle'         => 'Subida y seguimiento del informe de prácticas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:document-arrow-up',
                'status'           => 1,
                'moduleOrder'      => 4,
                'link'             => '/homeScreen/docs/informe-practicas',
                'parent_module_id' => 2,
            ],

            // ── 3. SEGUIMIENTO Y EVALUACIÓN (parent_module_id: 3) ─────────
            [
                'title'            => 'Reportes de Desempeño',
                'code'             => '04',
                'subtitle'         => 'Reportes e indicadores de prácticas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:chart-bar',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/seguimiento/desempeno',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Evaluaciones',
                'code'             => '05',
                'subtitle'         => 'Evaluaciones de prácticas preprofesionales',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-check',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/seguimiento/evaluaciones',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Validación de Documentos',
                'code'             => '06',
                'subtitle'         => 'Validación y aprobación de documentos',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:document-check',
                'status'           => 1,
                'moduleOrder'      => 3,
                'link'             => '/homeScreen/seguimiento/validacion-documentos',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Visitas de Supervisión',
                'code'             => 'VIS-01',
                'subtitle'         => 'Programación y seguimiento de visitas a empresas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:map-pin',
                'status'           => 1,
                'moduleOrder'      => 4,
                'link'             => '/homeScreen/seguimiento/visitas',
                'parent_module_id' => 3,
            ],

            // ── 4. CONFIGURACIÓN (parent_module_id: 4) ────────────────────
            [
                'title'            => 'Roles',
                'code'             => '07',
                'subtitle'         => 'Gestión de roles y permisos del sistema',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:shield-check',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/setup/roles',
                'parent_module_id' => 4,
            ],
            [
                'title'            => 'Módulos Padres',
                'code'             => '08',
                'subtitle'         => 'Gestión de secciones del menú',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:squares-2x2',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/setup/parent-module',
                'parent_module_id' => 4,
            ],
            [
                'title'            => 'Módulos',
                'code'             => '09',
                'subtitle'         => 'Gestión de ítems del menú',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:view-columns',
                'status'           => 1,
                'moduleOrder'      => 3,
                'link'             => '/homeScreen/setup/module',
                'parent_module_id' => 4,
            ],
            [
                'title'            => 'Usuarios',
                'code'             => 'USR-01',
                'subtitle'         => 'Gestión de usuarios del sistema',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:users',
                'status'           => 1,
                'moduleOrder'      => 4,
                'link'             => '/homeScreen/setup/users',
                'parent_module_id' => 4,
            ],
            [
                'title'            => 'Empresas',
                'code'             => 'EMP-01',
                'subtitle'         => 'Gestión de empresas colaboradoras',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:building-office',
                'status'           => 1,
                'moduleOrder'      => 5,
                'link'             => '/homeScreen/setup/empresas',
                'parent_module_id' => 4,
            ],
        ];

        foreach ($modules as $data) {
            Module::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
