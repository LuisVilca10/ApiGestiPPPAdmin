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
                'name' => 'Luis Administrador',
                'last_name' => 'de Sistema',
                'code' => '202420111',
                'photo_url' => 'ADM001.jpg',
                'username' => 'luis.admin',
                'password' => Hash::make('admin123'),
            ]
        );

        // Asignar rol Admin
        $admin->assignRole('Admin');
        // Crear usuario Alex
        $alex = User::firstOrCreate(
            ['email' => 'alex.mmm@example.com'],
            [
                'name' => 'Alex',
                'last_name' => 'mmm',
                'code' => '202129932',
                'photo_url' => 'USR001.jpg',
                'username' => 'alex.mmm',
                'password' => Hash::make('12345678'),
            ]
        );

        // Asignar roles a Alex
        $alex->assignRole('Admin', 'Estudiante');
    }
}
