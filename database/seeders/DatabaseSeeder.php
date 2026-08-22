<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CentralDatabaseSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(TenantSeeder::class);
        $this->call(JymTenantSeeder::class);
        $this->call(CasaAngelTenantSeeder::class);

    }
}
