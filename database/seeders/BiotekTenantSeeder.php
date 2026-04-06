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
            'nombre' => 'Taller de fotografia cientifica',
            'fecha' => '2026-04-06',
        ], [
            'duracion' => '2 horas',
        ]);

        $estudiante = BiotekEstudiante::query()->firstOrCreate([
            'identificacion' => 'BTK-001',
        ], [
            'nombres' => 'Laura',
            'apellidos' => 'Cortes',
        ]);

        $estudiante->talleres()->sync([
            $taller->id => [
                'pago' => 120000,
                'abono' => 50000,
                'debe' => 70000,
                'saldo_total' => 70000,
            ],
        ]);

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