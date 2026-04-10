<?php

namespace Database\Seeders;

use App\Models\BiotekEstudiante;
use App\Models\Multimedia;
use App\Models\Taller;
use Illuminate\Database\Seeder;

class BiotekTenantSeeder extends Seeder
{
    public function run(): void
    {
        $taller = Taller::query()->firstOrCreate([
            'codigo' => 'BTK-FOTOCI-20260406',
        ], [
            'fecha' => '2026-04-06',
            'duration_seconds' => 7200,
        ]);

        $estudiante = BiotekEstudiante::query()->firstOrCreate([
            'identificacion' => 'BTK-001',
        ], [
            'nombres' => 'Laura',
            'apellidos' => 'Cortes',
        ]);

        $estudiante->talleres()->syncWithoutDetaching([$taller->id]);

        $multimedia = Multimedia::on('tenant_biotek')->firstOrCreate([
            'url' => '/storage/img/biotek/estudiantes/laura-cortes.jpg',
            'preview_url' => '/storage/img/biotek/estudiantes/laura-cortes.jpg',
            'type' => 'image',
        ], [
            'mime_type' => 'image/jpeg',
        ]);

        $estudiante->multimedias()->syncWithoutDetaching([$multimedia->id]);
    }
}