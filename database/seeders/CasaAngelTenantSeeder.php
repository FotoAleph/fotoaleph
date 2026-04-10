<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Color;
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
            $normalizedItem = $this->normalizeItem($item);
            $projectName = $normalizedItem['project'];
            $alt = $normalizedItem['name'];
            $imagePath = $normalizedItem['url'];
            $previewPath = $normalizedItem['preview_url'];

            $ocasion = Ocasion::query()->firstOrCreate(
                ['nombre' => $normalizedItem['ocasion']],
                ['descripcion' => $normalizedItem['ocasion']],
            );

            $tematica = Tematica::query()->firstOrCreate(
                ['nombre' => $normalizedItem['tematica']],
                ['descripcion' => $normalizedItem['tematica']],
            );

            $colorName = $this->inferColorName(implode(' ', array_filter([
                $alt,
                $normalizedItem['tematica'],
                $projectName,
            ])));

            $color = $colorName
                ? Color::query()->firstOrCreate(
                    ['nombre' => $colorName],
                    ['descripcion' => $colorName],
                )
                : null;

            $evento = Evento::query()->firstOrCreate(
                ['nombre' => Str::title($alt)],
                [
                    'descripcion' => 'Proyecto: '.$projectName.'. Ocasión: '.$normalizedItem['ocasion'].'. Temática: '.$normalizedItem['tematica'].'.',
                    'fecha_evento' => $normalizedItem['fecha_evento'],
                    'entregado' => $normalizedItem['entregado'],
                    'codigo' => $normalizedItem['codigo'],
                ],
            );

            $evento->forceFill([
                'descripcion' => $evento->descripcion ?: 'Proyecto: '.$projectName.'. Ocasión: '.$normalizedItem['ocasion'].'. Temática: '.$normalizedItem['tematica'].'.',
                'fecha_evento' => $evento->fecha_evento ?: $normalizedItem['fecha_evento'],
                'entregado' => (bool) ($evento->entregado ?: $normalizedItem['entregado']),
                'codigo' => $evento->codigo ?: $normalizedItem['codigo'],
            ])->save();

            if ($imagePath === '') {
                continue;
            }

            $multimedia = Multimedia::on('tenant_casa_angel')->firstOrCreate(
                [
                    'url' => $imagePath,
                    'preview_url' => $previewPath,
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($imagePath),
                    'alt' => sprintf(
                        'Evento %s del %s foto %s',
                        $evento->nombre,
                        $evento->fecha_evento?->toDateString() ?? now()->toDateString(),
                        basename($imagePath),
                    ),
                    'aspect_ratio' => $this->inferAspectRatio($imagePath),
                    'nivel' => 0,
                ],
            );

            $multimedia->forceFill([
                'alt' => $multimedia->alt ?: sprintf(
                    'Evento %s del %s foto %s',
                    $evento->nombre,
                    $evento->fecha_evento?->toDateString() ?? now()->toDateString(),
                    basename($imagePath),
                ),
                'preview_url' => $multimedia->preview_url ?: $previewPath,
                'aspect_ratio' => $multimedia->aspect_ratio ?: $this->inferAspectRatio($imagePath),
                'nivel' => max(0, (int) ($multimedia->nivel ?? 0)),
            ])->save();

            $evento->multimedias()->syncWithoutDetaching([$multimedia->id => ['cantidad' => 0]]);
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

    private function normalizeItem(array $item): array
    {
        $name = trim((string) ($item['alt'] ?? $item['nombre'] ?? 'Vitrina Casa Angel'));
        $projectKey = trim((string) ($item['proyecto'] ?? ''));
        $code = trim((string) ($item['codigo'] ?? ''));
        $projectSource = $projectKey !== '' ? $projectKey : ($code !== '' ? $code : $name);

        return [
            'project' => $projectKey !== ''
                ? $this->decodeProject($projectSource)
                : Str::title(str_replace(['-', '_'], ' ', $projectSource)),
            'name' => $name,
            'url' => trim((string) ($item['foto'] ?? $item['url'] ?? '')),
            'preview_url' => trim((string) ($item['url_preview'] ?? $item['preview'] ?? $item['foto'] ?? $item['url'] ?? '')),
            'ocasion' => Str::title(trim((string) ($item['ocasion'] ?? 'General')) ?: 'General'),
            'tematica' => Str::title(trim((string) ($item['tematica'] ?? 'General')) ?: 'General'),
            'fecha_evento' => $this->normalizeDate($item['fecha'] ?? null),
            'entregado' => Str::lower(trim((string) ($item['estado'] ?? ''))) === 'entregado',
            'codigo' => $code !== '' ? $code : null,
        ];
    }

    private function normalizeDate(mixed $value): ?string
    {
        $date = trim((string) $value);

        return $date !== '' ? $date : null;
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

    private function inferColorName(string $text): ?string
    {
        $haystack = Str::lower($text);

        foreach ([
            'rosa', 'rosado', 'rojo', 'azul', 'verde', 'dorado', 'plateado', 'blanco',
            'negro', 'lila', 'morado', 'violeta', 'amarillo', 'naranja', 'coral',
            'beige', 'marfil', 'champagne', 'cobre', 'fucsia', 'turquesa',
        ] as $color) {
            if (Str::contains($haystack, $color)) {
                return Str::title($color);
            }
        }

        return null;
    }

    private function inferAspectRatio(string $path): string
    {
        return Str::contains(Str::lower($path), ['9-16', 'vertical'])
            ? '9:16'
            : '16:9';
    }
}
