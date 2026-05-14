<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'               => 'Administrador',
                'last_name'          => 'de Sistema',
                'code'               => '202420111',
                'photo_url'          => 'ADM001.jpg',
                'username'           => 'luis.admin',
                'password'           => Hash::make('admin123'),
                'academic_cycle'     => 'I',
                'hours_of_practice'  => 0,
            ]
        );

        $admin->assignRole('Admin');

        $alex = User::firstOrCreate(
            ['email' => 'alex.mmm@example.com'],
            [
                'name'               => 'Alessandro Pastor',
                'last_name'          => 'Mamani Mamani',
                'code'               => '202129932',
                'photo_url'          => 'USR001.jpg',
                'username'           => 'alex.mmm',
                'password'           => Hash::make('12345678'),
                'academic_cycle'     => 'VI',
                'hours_of_practice'  => 0,
            ]
        );

        $alex->assignRole('Estudiante');

        $students = [
            [
                'email'                => 'tammya.suaña@example.com',
                'name'                 => 'Tammya Maricielo',
                'last_name'            => 'Suaña Ortega',
                'code'                 => '202130001',
                'username'             => 'tammya.suaña',
                'photo_url'            => 'USR002.jpg',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VI',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'carlos.rios@example.com',
                'name'                 => 'Carlos',
                'last_name'            => 'Ríos Mendoza',
                'code'                 => '202130002',
                'username'             => 'carlos.rios',
                'photo_url'            => 'USR003.jpg',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VII',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'ana.flores@example.com',
                'name'                 => 'Ana',
                'last_name'            => 'Flores Paredes',
                'code'                 => '202130003',
                'username'             => 'ana.flores',
                'photo_url'            => 'USR004.jpg',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VIII',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'pedro.huanca@example.com',
                'name'                 => 'Pedro',
                'last_name'            => 'Huanca Quispe',
                'code'                 => '202130004',
                'username'             => 'pedro.huanca',
                'photo_url'            => 'USR005.jpg',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'VI',
                'hours_of_practice'    => 0,
                'must_change_password' => true,
            ],
            [
                'email'                => 'lucia.mamani@example.com',
                'name'                 => 'Lucía',
                'last_name'            => 'Mamani Condori',
                'code'                 => '202130005',
                'username'             => 'lucia.mamani',
                'photo_url'            => 'USR006.jpg',
                'password'             => Hash::make('12345678'),
                'academic_cycle'       => 'IX',
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
