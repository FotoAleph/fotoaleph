<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\Multimedia;
use Illuminate\Database\Seeder;

class SportBogotaTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            ['nombre' => 'Aron Fernandez', 'categoria' => '2019', 'foto' => '/storage/img/sport_bogota/estudiantes/AronFernandez.jpg'],
            ['nombre' => 'Juan Esteban Caicedo Ortiz', 'categoria' => '2015-16', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6452.JPG'],
            ['nombre' => 'Tomas Prieto', 'categoria' => '2017', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6757.JPG'],
            ['nombre' => 'Luis Santiago Peña Torres', 'categoria' => '2017', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6760.JPG'],
            ['nombre' => 'Angel Robayo', 'categoria' => '2019', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6771.JPG'],
            ['nombre' => 'Samuel Niño', 'categoria' => '2015', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6777.JPG'],
            ['nombre' => 'Martin Guerrero Bachiller', 'categoria' => '2019', 'foto' => '/storage/img/sport_bogota/estudiantes/_DSC6781.JPG'],
        ];

        foreach ($students as $studentData) {
            $estudiante = Estudiante::query()->updateOrCreate(
                [
                    'nombre' => $studentData['nombre'],
                    'categoria' => $studentData['categoria'],
                ],
                [],
            );

            if (!empty($studentData['foto'])) {
                $multimedia = Multimedia::on('tenant_sport_bogota')->firstOrCreate(
                    [
                        'url' => $studentData['foto'],
                        'preview_url' => $studentData['foto'],
                        'type' => 'image',
                    ],
                    [
                        'mime_type' => $this->guessMimeType($studentData['foto']),
                    ],
                );

                $estudiante->multimedias()->sync([$multimedia->id]);
                continue;
            }

            $estudiante->multimedias()->sync([]);
        }
    }

    private function guessMimeType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
