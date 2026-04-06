<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$runMigrationCommand = function (string $command, string $database, string $path): void {
    Artisan::call($command, [
        '--database' => $database,
        '--path' => $path,
        '--realpath' => true,
    ]);

    $this->output->write(Artisan::output());
};

$runSeederCommand = function (string $database, string $class): void {
    Artisan::call('db:seed', [
        '--database' => $database,
        '--class' => $class,
        '--force' => true,
    ]);

    $this->output->write(Artisan::output());
};

$runFreshCommand = function (string $database, string $path): void {
    Artisan::call('migrate:fresh', [
        '--database' => $database,
        '--path' => $path,
        '--realpath' => true,
        '--force' => true,
    ]);

    $this->output->write(Artisan::output());
};

Artisan::command('tenancy:migrate-central', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate', 'tenant_central', database_path('migrations/central'));
})->purpose('Run central platform migrations only.');

Artisan::command('tenancy:migrate-jym', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate', 'tenant_jym', database_path('migrations/tenant_jym'));
})->purpose('Run Vidrios y Estructuras JyM tenant migrations only.');

Artisan::command('tenancy:migrate-casa-angel', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate', 'tenant_casa_angel', database_path('migrations/tenant_casa_angel'));
})->purpose('Run Casa Angel tenant migrations only.');

Artisan::command('tenancy:migrate-biotek', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate', 'tenant_biotek', database_path('migrations/tenant_biotek'));
})->purpose('Run Biotek tenant migrations only.');

Artisan::command('tenancy:migrate-sport-bogota', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate', 'tenant_sport_bogota', database_path('migrations/tenant_sport_bogota'));
})->purpose('Run Sport Bogota tenant migrations only.');

Artisan::command('tenancy:status-central', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate:status', 'tenant_central', database_path('migrations/central'));
})->purpose('Show migration status for the central platform database.');

Artisan::command('tenancy:status-jym', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate:status', 'tenant_jym', database_path('migrations/tenant_jym'));
})->purpose('Show migration status for the Vidrios y Estructuras JyM database.');

Artisan::command('tenancy:status-casa-angel', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate:status', 'tenant_casa_angel', database_path('migrations/tenant_casa_angel'));
})->purpose('Show migration status for the Casa Angel database.');

Artisan::command('tenancy:status-biotek', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate:status', 'tenant_biotek', database_path('migrations/tenant_biotek'));
})->purpose('Show migration status for the Biotek database.');

Artisan::command('tenancy:status-sport-bogota', function () use ($runMigrationCommand) {
    $runMigrationCommand->call($this, 'migrate:status', 'tenant_sport_bogota', database_path('migrations/tenant_sport_bogota'));
})->purpose('Show migration status for the Sport Bogota database.');

Artisan::command('tenancy:fresh-central', function () use ($runFreshCommand) {
    $runFreshCommand->call($this, 'tenant_central', database_path('migrations/central'));
})->purpose('Fresh central platform schema only.');

Artisan::command('tenancy:fresh-jym', function () use ($runFreshCommand) {
    $runFreshCommand->call($this, 'tenant_jym', database_path('migrations/tenant_jym'));
})->purpose('Fresh Vidrios y Estructuras JyM schema only.');

Artisan::command('tenancy:fresh-casa-angel', function () use ($runFreshCommand) {
    $runFreshCommand->call($this, 'tenant_casa_angel', database_path('migrations/tenant_casa_angel'));
})->purpose('Fresh Casa Angel schema only.');

Artisan::command('tenancy:fresh-biotek', function () use ($runFreshCommand) {
    $runFreshCommand->call($this, 'tenant_biotek', database_path('migrations/tenant_biotek'));
})->purpose('Fresh Biotek schema only.');

Artisan::command('tenancy:fresh-sport-bogota', function () use ($runFreshCommand) {
    $runFreshCommand->call($this, 'tenant_sport_bogota', database_path('migrations/tenant_sport_bogota'));
})->purpose('Fresh Sport Bogota schema only.');

Artisan::command('tenancy:seed-central', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_central', \Database\Seeders\CentralDatabaseSeeder::class);
})->purpose('Seed the central platform database only.');

Artisan::command('tenancy:seed-jym', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_jym', \Database\Seeders\JymTenantSeeder::class);
})->purpose('Seed the Vidrios y Estructuras JyM domain only.');

Artisan::command('tenancy:seed-casa-angel', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_casa_angel', \Database\Seeders\CasaAngelTenantSeeder::class);
})->purpose('Seed the Casa Angel domain only.');

Artisan::command('tenancy:seed-biotek', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_biotek', \Database\Seeders\BiotekTenantSeeder::class);
})->purpose('Seed the Biotek domain only.');

Artisan::command('tenancy:seed-sport-bogota', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_sport_bogota', \Database\Seeders\SportBogotaTenantSeeder::class);
})->purpose('Seed the Sport Bogota domain only.');

Artisan::command('tenancy:seed-vitrinas', function () use ($runSeederCommand) {
    $runSeederCommand->call($this, 'tenant_central', \Database\Seeders\VitrinaCurationSeeder::class);
})->purpose('Seed central vitrinas curated from tenant project and event media.');

Artisan::command('tenancy:reset-demo', function () use ($runFreshCommand, $runSeederCommand) {
    $runFreshCommand->call($this, 'tenant_central', database_path('migrations/central'));
    $runFreshCommand->call($this, 'tenant_jym', database_path('migrations/tenant_jym'));
    $runFreshCommand->call($this, 'tenant_casa_angel', database_path('migrations/tenant_casa_angel'));
    $runFreshCommand->call($this, 'tenant_biotek', database_path('migrations/tenant_biotek'));
    $runFreshCommand->call($this, 'tenant_sport_bogota', database_path('migrations/tenant_sport_bogota'));

    $runSeederCommand->call($this, 'tenant_central', \Database\Seeders\CentralDatabaseSeeder::class);
    $runSeederCommand->call($this, 'tenant_jym', \Database\Seeders\JymTenantSeeder::class);
    $runSeederCommand->call($this, 'tenant_casa_angel', \Database\Seeders\CasaAngelTenantSeeder::class);
    $runSeederCommand->call($this, 'tenant_biotek', \Database\Seeders\BiotekTenantSeeder::class);
    $runSeederCommand->call($this, 'tenant_sport_bogota', \Database\Seeders\SportBogotaTenantSeeder::class);
    $runSeederCommand->call($this, 'tenant_central', \Database\Seeders\VitrinaCurationSeeder::class);
})->purpose('Rebuild and reseed central, JyM, Casa Angel, Biotek, Sport Bogota, and vitrina demo data in order.');