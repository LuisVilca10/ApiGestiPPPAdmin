<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ────────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Administrador',
                'last_name'         => 'de Sistema',
                'code'              => '202420111',
                'username'          => 'luis.admin',
                'password'          => Hash::make('admin123'),
                'academic_cycle'    => 'I',
                'career'            => 'Administración',
                'hours_of_practice' => 0,
            ]
        );
        $admin->assignRole('Admin');

        // ── Encargado de Prácticas ───────────────────────────────────────────
        $encargado = User::firstOrCreate(
            ['email' => 'encargado@example.com'],
            [
                'name'              => 'Encargado',
                'last_name'         => 'de Prácticas',
                'code'              => '202420112',
                'username'          => 'encargado.ppp',
                'password'          => Hash::make('12345678'),
                'academic_cycle'    => 'I',
                'career'            => 'Administración',
                'hours_of_practice' => 0,
            ]
        );
        $encargado->assignRole('Encargado de Practicas');

        // ── Coordinador ──────────────────────────────────────────────────────
        $coordinador = User::firstOrCreate(
            ['email' => 'coordinador@example.com'],
            [
                'name'              => 'Coordinador',
                'last_name'         => 'EP Administración',
                'code'              => '202420113',
                'username'          => 'coordinador.ep',
                'password'          => Hash::make('12345678'),
                'academic_cycle'    => 'I',
                'career'            => 'Administración',
                'hours_of_practice' => 0,
            ]
        );
        $coordinador->assignRole('Coordinador');

        // ── Docente ──────────────────────────────────────────────────────────
        $docente = User::firstOrCreate(
            ['email' => 'docente@example.com'],
            [
                'name'              => 'Amed',
                'last_name'         => 'Vargas Martínez',
                'code'              => '202420114',
                'username'          => 'amed.vargas',
                'password'          => Hash::make('12345678'),
                'academic_cycle'    => 'I',
                'career'            => 'Administración',
                'hours_of_practice' => 0,
            ]
        );
        $docente->assignRole('Docente');

        // ── Estudiante principal (Alessandro) ────────────────────────────────
        $alex = User::firstOrCreate(
            ['email' => 'alex.mmm@example.com'],
            [
                'name'              => 'Alessandro Pastor',
                'last_name'         => 'Mamani Mamani',
                'code'              => '202129932',
                'username'          => 'alex.mmm',
                'password'          => Hash::make('12345678'),
                'academic_cycle'    => 'VI',
                'dni'               => '74512345',
                'phone'             => '987654321',
                'career'            => 'Administración',
                'hours_of_practice' => 0,
            ]
        );
        $alex->assignRole('Estudiante');

        // ── Estudiantes de ejemplo ───────────────────────────────────────────
        $students = [
            [
                'email'                => 'tammya.suaña@example.com',
                'name'                 => 'Tammya Maricielo',
                'last_name'            => 'Suaña Ortega',
                'code'                 => '202130001',
                'username'             => 'tammya.su',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VI',
                'dni'                  => '74500001',
                'phone'                => '987000001',
                'career'               => 'Administración',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'carlos.rios@example.com',
                'name'                 => 'Carlos',
                'last_name'            => 'Ríos Mendoza',
                'code'                 => '202130002',
                'username'             => 'carlos.rios',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VII',
                'dni'                  => '74500002',
                'phone'                => '987000002',
                'career'               => 'Administración',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'ana.flores@example.com',
                'name'                 => 'Ana',
                'last_name'            => 'Flores Paredes',
                'code'                 => '202130003',
                'username'             => 'ana.flores',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VIII',
                'dni'                  => '74500003',
                'phone'                => '987000003',
                'career'               => 'Administración',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'pedro.huanca@example.com',
                'name'                 => 'Pedro',
                'last_name'            => 'Huanca Quispe',
                'code'                 => '202130004',
                'username'             => 'pedro.huanca',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VI',
                'dni'                  => '74500004',
                'phone'                => '987000004',
                'career'               => 'Administración',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'lucia.mamani@example.com',
                'name'                 => 'Lucía',
                'last_name'            => 'Mamani Condori',
                'code'                 => '202130005',
                'username'             => 'lucia.mamani',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'IX',
                'dni'                  => '74500005',
                'phone'                => '987000005',
                'career'               => 'Administración',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
        ];

        foreach ($students as $data) {
            $email = $data['email'];
            unset($data['email']);
            $student = User::firstOrCreate(['email' => $email], $data);
            $student->assignRole('Estudiante');
        }
    }
}
