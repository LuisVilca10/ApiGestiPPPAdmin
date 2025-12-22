<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModuleRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtén los roles
        $adminRole   = Role::where('name', 'Admin')->first();
        $coordinadorRole = Role::where('name', 'Coordinador')->first();
        $studentRole = Role::where('name', 'Estudiante')->first();
        //$userRole    = Role::where('name', 'usuario')->first();

        // =========================
        // Admin -> TODOS LOS MÓDULOS
        // =========================
        $modulosParaAdmin = Module::all();
        foreach ($modulosParaAdmin as $modulo) {
            $modulo->roles()->syncWithoutDetaching([$adminRole->id]);
        }

        // =========================
        // Estudiante -> Trámites + Seguimiento
        // =========================
        $modulosParaEstudiante = Module::whereIn('code', ['02', '03', '04'])->get();
        foreach ($modulosParaEstudiante as $modulo) {
            $modulo->roles()->syncWithoutDetaching([$studentRole->id]);
        }

        // =========================
        // Usuario -> Solo Bandeja de Documentos
        // =========================
        //$modulosParaUsuario = Module::whereIn('code', ['01'])->get();
        //foreach ($modulosParaUsuario as $modulo) {
        //    $modulo->roles()->syncWithoutDetaching([$userRole->id]);
        //}
    }
}
