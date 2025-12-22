<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Hijos de Documentos y Trámite (01)
            [
                'title'            => 'CP Y Mis Practicas',
                'code'             => '01',
                'subtitle'         => 'Gestión de trámites',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-document',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/docs/practices',
                'parent_module_id' => 1,
            ],
            [
                'title'            => 'Bitacora de mis Documentos',
                'code'             => '02',
                'subtitle'         => 'Gestión de trámites',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-check',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/docs/bitacora',
                'parent_module_id' => 1,
            ],

            // Hijos de Seguimiento y Evaluación (02)
            [
                'title'            => 'Reportes de desempeño',
                'code'             => '03',
                'subtitle'         => 'Módulo de reportes e indicadores',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:chart-bar',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/seguimiento/desempeno',
                'parent_module_id' => 2,
            ],
            [
                'title'            => 'Evaluaciones',
                'code'             => '04',
                'subtitle'         => 'Evaluaciones de prácticas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-check',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/seguimiento/evaluaciones',
                'parent_module_id' => 2,
            ],
            [
                'title'            => 'Validación de Documentos',
                'code'             => '05',
                'subtitle'         => 'Evaluaciones de prácticas',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-check',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/seguimiento/validacion-documentos',
                'parent_module_id' => 2,
            ],

            // Hijos de Configuración (03)
            [
                'title'            => 'Usuarios',
                'code'             => '05',
                'subtitle'         => 'Gestión de usuarios del sistema',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:user-group',
                'status'           => 1,
                'moduleOrder'      => 1,
                'link'             => '/homeScreen/setup/users',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Roles',
                'code'             => '06',
                'subtitle'         => 'Gestión de roles y permisos',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:shield-check',
                'status'           => 1,
                'moduleOrder'      => 2,
                'link'             => '/homeScreen/setup/roles',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Configuración General',
                'code'             => '07',
                'subtitle'         => 'Parámetros del sistema',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:cog-6-tooth',
                'status'           => 1,
                'moduleOrder'      => 3,
                'link'             => '/homeScreen/setup/config',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Modulos Padres',
                'code'             => '08',
                'subtitle'         => 'Gestión de módulos padres',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:clipboard-document',
                'status'           => 1,
                'moduleOrder'      => 4,
                'link'             => '/homeScreen/setup/parent-module',
                'parent_module_id' => 3,
            ],
            [
                'title'            => 'Modulos',
                'code'             => '09',
                'subtitle'         => 'Gestión de módulos',
                'type'             => 'basic',
                'icon'             => 'heroicons_outline:folder-open',
                'status'           => 1,
                'moduleOrder'      => 5,
                'link'             => '/homeScreen/setup/module',
                'parent_module_id' => 3,
            ],
        ];

        foreach ($modules as $data) {
            Module::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
