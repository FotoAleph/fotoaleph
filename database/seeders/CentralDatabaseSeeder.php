<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CentralDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'test@dinamycode.com'], [
            'name' => 'Test User',
            'email' => 'test@dinamycode.com',
            'role' => 'cliente',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::updateOrCreate(['email' => 'admin@dinamycode.com'], [
            'name' => 'Admin User',
            'email' => 'admin@dinamycode.com',
            'role' => 'admin',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::updateOrCreate(['email' => 'fotoaleph@dinamycode.com'], [
            'name' => 'Carlos Alberto Ramirez',
            'email' => 'fotoaleph@dinamycode.com',
            'role' => 'coordinador',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::updateOrCreate(['email' => 'sportbogotafc@gmail.com'], [
            'name' => 'Sport Bogota',
            'email' => 'sportbogotafc@gmail.com',
            'role' => 'coordinador',
            'password' => bcrypt('PassDinamycode!'),
        ]);

        $this->call(SocialNetworkTypesSeeder::class);

    }
}
