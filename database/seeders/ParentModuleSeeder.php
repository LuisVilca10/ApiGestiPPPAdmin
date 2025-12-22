<?php

namespace Database\Seeders;

use App\Models\ParentModule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParentModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentmodule = [
            [
                'title'        => 'Practicas y Documentos',
                'code'         => '01',
                'subtitle'     => 'Gestión de Practicas y Documentos',
                'type'         => 'collapsable',
                'icon'         => 'heroicons_outline:user-group',
                'status'       => 1,
                'moduleOrder'  => 1,
                'link'         => '/example',
            ],
            [
                'title'        => 'Seguimiento y Evaluación',
                'code'         => '02',
                'subtitle'     => 'Gestión de Seguimiento y Evaluación',
                'type'         => 'collapsable',
                'icon'         => 'heroicons_outline:user-group',
                'status'       => 1,
                'moduleOrder'  => 2,
                'link'         => '/example',
            ],
            [
                'title'        => 'Configuración',
                'code'         => '03',
                'subtitle'     => 'Gestión de Configuración',
                'type'         => 'collapsable',
                'icon'         => 'heroicons_outline:user-group',
                'status'       => 1,
                'moduleOrder'  => 3,
                'link'         => '/example',
            ]
        ];
        foreach ($parentmodule as $data) {
            ParentModule::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
