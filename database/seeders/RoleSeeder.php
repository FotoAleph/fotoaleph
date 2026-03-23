<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@fotoaleph.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Crear usuario empleado
        User::create([
            'name' => 'Employee User',
            'email' => 'employee@fotoaleph.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
            'email_verified_at' => now(),
        ]);

        // Crear usuario cliente
        User::create([
            'name' => 'Client User',
            'email' => 'client@fotoaleph.com',
            'password' => Hash::make('password'),
            'role' => 'cliente',
            'email_verified_at' => now(),
        ]);

        // Crear más usuarios de prueba
        User::factory(5)->create(['role' => 'cliente']);
        User::factory(3)->create(['role' => 'empleado']);
    }
}
