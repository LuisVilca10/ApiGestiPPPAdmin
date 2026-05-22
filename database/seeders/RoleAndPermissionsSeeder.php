<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            // Usuarios
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'editar_perfil',
            'editar_roles',
            // Módulos
            'formulario',
            // Prácticas
            'gestionar_practicas',    // ver todas las prácticas, aprobar/rechazar
            'gestionar_documentos',   // aprobar/rechazar documentos
            'subir_ficha_visita',     // el Docente sube fichas de visita
            'gestionar_visitas',      // el Docente registra y edita visitas
            // Reportes y cierre
            'ver_reportes',           // Coordinador: informes globales
            'registrar_sustentacion', // Admin/Coordinador: registra resultado de sustentación
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'api']);
        }

        // ── Admin ────────────────────────────────────────────────────────────
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'api'],
            ['description' => 'Administrador con acceso total al sistema']
        );
        $admin->syncPermissions(Permission::all());

        // ── Encargado de Practicas ───────────────────────────────────────────
        $encargado = Role::firstOrCreate(
            ['name' => 'Encargado de Practicas', 'guard_name' => 'api'],
            ['description' => 'Revisa y aprueba documentos; gestiona el proceso completo de prácticas']
        );
        $encargado->syncPermissions([
            'editar_perfil',
            'formulario',
            'gestionar_practicas',
            'gestionar_documentos',
            'ver_reportes',
        ]);

        // ── Coordinador ──────────────────────────────────────────────────────
        $coordinador = Role::firstOrCreate(
            ['name' => 'Coordinador', 'guard_name' => 'api'],
            ['description' => 'Genera informes globales y firma la sustentación final']
        );
        $coordinador->syncPermissions([
            'editar_perfil',
            'formulario',
            'ver_reportes',
            'registrar_sustentacion',
        ]);

        // ── Docente ──────────────────────────────────────────────────────────
        $docente = Role::firstOrCreate(
            ['name' => 'Docente', 'guard_name' => 'api'],
            ['description' => 'Realiza visitas a empresas y sube fichas de evaluación']
        );
        $docente->syncPermissions([
            'editar_perfil',
            'formulario',
            'subir_ficha_visita',
            'gestionar_visitas',
        ]);

        // ── Estudiante ───────────────────────────────────────────────────────
        $estudiante = Role::firstOrCreate(
            ['name' => 'Estudiante', 'guard_name' => 'api'],
            ['description' => 'Alumno habilitado para registrar y gestionar sus prácticas']
        );
        $estudiante->syncPermissions([
            'editar_perfil',
            'formulario',
        ]);
    }
}
