<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

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
        user::factory()->create([
            'name' => 'Carlos Alberto Ramirez',
            'email' => 'fotoaleph@dinamycode.com',
            'role' => 'coordinador',
            'password' => bcrypt('dinamycodeDC*'),
        ]);
        
        User::factory()->create([
            'name' => 'Sport Bogota',
            'email' => 'sportbogotafc@gmail.com ',
            'role' => 'coordinador',
            'password' => bcrypt('PassDinamycode!'),
        ]);


    $this->call(SocialNetworkTypesSeeder::class);
    $this->call(TenantSeeder::class);

    }
}
