<?php

namespace Database\Seeders;

use App\Models\ParentModule;
use Illuminate\Database\Seeder;

class ParentModuleSeeder extends Seeder
{
    public function run(): void
    {
        $parentmodule = [
            [
                'title'       => 'Inicio de Prácticas',
                'code'        => 'PM-INICIO',
                'subtitle'    => 'Registro e inicio de prácticas preprofesionales',
                'type'        => 'collapsable',
                'icon'        => 'heroicons_outline:home',
                'status'      => 1,
                'moduleOrder' => 1,
                'link'        => '/example',
            ],
            [
                'title'       => 'Documentos de Prácticas',
                'code'        => 'PM-DOCS',
                'subtitle'    => 'Gestión de documentos de prácticas',
                'type'        => 'collapsable',
                'icon'        => 'heroicons_outline:document-duplicate',
                'status'      => 1,
                'moduleOrder' => 2,
                'link'        => '/example',
            ],
            [
                'title'       => 'Seguimiento y Evaluación',
                'code'        => 'PM-SEGUIMIENTO',
                'subtitle'    => 'Seguimiento y evaluación de prácticas',
                'type'        => 'collapsable',
                'icon'        => 'heroicons_outline:chart-bar',
                'status'      => 1,
                'moduleOrder' => 3,
                'link'        => '/example',
            ],
            [
                'title'       => 'Configuración',
                'code'        => 'PM-CONFIG',
                'subtitle'    => 'Configuración del sistema',
                'type'        => 'collapsable',
                'icon'        => 'heroicons_outline:cog-6-tooth',
                'status'      => 1,
                'moduleOrder' => 4,
                'link'        => '/example',
            ],
        ];

        foreach ($parentmodule as $data) {
            ParentModule::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
