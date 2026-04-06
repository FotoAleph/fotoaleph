<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Multimedia;
use App\Models\Ocasion;
use App\Models\Tematica;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CasaAngelTenantSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->galleryItems() as $item) {
            $projectName = $this->decodeProject($item['proyecto']);
            $alt = trim((string) ($item['alt'] ?? 'Vitrina Casa Angel'));
            $imagePath = trim((string) ($item['foto'] ?? ''));

            $ocasion = Ocasion::query()->firstOrCreate(
                ['nombre' => Str::title((string) $item['ocasion'])],
                ['descripcion' => Str::title((string) $item['ocasion'])],
            );

            $tematica = Tematica::query()->firstOrCreate(
                ['nombre' => Str::title((string) $item['tematica'])],
                ['descripcion' => Str::title((string) $item['tematica'])],
            );

            $evento = Evento::query()->firstOrCreate(
                ['nombre' => Str::title($alt)],
                [
                    'descripcion' => 'Proyecto: '.$projectName.'. Ocasión: '.Str::title((string) $item['ocasion']).'. Temática: '.Str::title((string) $item['tematica']).'.',
                ],
            );

            if ($imagePath === '') {
                continue;
            }

            $multimedia = Multimedia::query()->firstOrCreate(
                [
                    'url' => $imagePath,
                    'preview_url' => $imagePath,
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($imagePath),
                ],
            );

            $evento->multimedias()->syncWithoutDetaching([$multimedia->id]);
        }
    }

    private function galleryItems(): array
    {
        $json = file_get_contents(resource_path('utils/galeria.json'));

        return json_decode($json ?: '[]', true, 512, JSON_THROW_ON_ERROR);
    }

    private function decodeProject(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded !== false ? Str::title(str_replace(['_', '-'], ' ', $decoded)) : Str::title($value);
    }

    private function guessMimeType(string $path): string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}
