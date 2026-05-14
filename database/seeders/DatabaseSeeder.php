<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Roles y permisos — base de todo (Admin, Estudiante)
            RoleAndPermissionsSeeder::class,

            // 2. Usuarios — necesita roles ya creados
            UserAdminSeeder::class,

            // 3. Módulos padre — secciones del menú (Prácticas, Seguimiento, Configuración)
            ParentModuleSeeder::class,

            // 4. Módulos — ítems del menú, necesita parent_modules
            ModuleSeeder::class,

            // 5. Asignación módulos → roles — necesita módulos y roles
            ModuleRoleSeeder::class,

            // 6. Prácticas de ejemplo — necesita usuarios y crea empresas automáticamente
            PracticeSeeder::class,

            // 7. Documentos de ejemplo — necesita prácticas ya creadas
            DocumentSeeder::class,
        ]);
    }
}
