<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CentralDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@dinamycode.com',
            'role' => 'cliente',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@dinamycode.com',
            'role' => 'admin',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::factory()->create([
            'name' => 'Carlos Alberto Ramirez',
            'email' => 'fotoaleph@dinamycode.com',
            'role' => 'coordinador',
            'password' => bcrypt('dinamycodeDC*'),
        ]);

        User::factory()->create([
            'name' => 'Sport Bogota',
            'email' => 'sportbogotafc@gmail.com',
            'role' => 'coordinador',
            'password' => bcrypt('PassDinamycode!'),
        ]);

        $this->call(SocialNetworkTypesSeeder::class);
        $this->call(TenantSeeder::class);
        $this->call(RoleSeeder::class);
    }
}
