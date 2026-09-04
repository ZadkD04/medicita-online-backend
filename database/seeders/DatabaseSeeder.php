<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $users = [
            [
                'name' => 'Administrador',
                'email' => 'Admin',
                'password' => 'admin',
                'role' => 'admin',
                'specialty' => null,
                'telefono' => null,
            ],
            [
                'name' => 'Dr. Alejandro Morales',
                'email' => 'doctor@medicita.com',
                'password' => 'password123',
                'role' => 'doctor',
                'specialty' => 'Medicina General',
                'telefono' => null,
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos@gmail.com',
                'password' => '123',
                'role' => 'paciente',
                'specialty' => null,
                'telefono' => '8888-1111',
            ],
            [
                'name' => 'Dra. Sofía Martínez',
                'email' => 'sofia.med@gmail.com',
                'password' => '123',
                'role' => 'doctor',
                'specialty' => 'Medicina General',
                'telefono' => '8888-2222',
            ],
            [
                'name' => 'Lucía Fernández',
                'email' => 'lucia@gmail.com',
                'password' => '123',
                'role' => 'paciente',
                'specialty' => null,
                'telefono' => '8888-3333',
            ],
            [
                'name' => 'Dr. Roberto Gómez',
                'email' => 'roberto.med@gmail.com',
                'password' => '123',
                'role' => 'doctor',
                'specialty' => 'Cardiología',
                'telefono' => '8888-4444',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        User::query()->each(function (User $user) {
            $user->syncRole($user->role ?: 'paciente');
        });
    }
}
