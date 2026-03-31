<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Multimedia;
use App\Models\Evento;
use App\Models\JymCategoria;
use App\Models\Ocasion;
use App\Models\Proyecto;
use App\Models\Tenant;
use App\Models\Tematica;
use App\Models\Vitrina;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VitrinaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (
            ! Schema::connection('tenant_jym')->hasTable('proyectos')
            || ! Schema::connection('tenant_casa_angel')->hasTable('eventos')
            || ! Schema::hasTable('vitrina_items')
        ) {
            return;
        }

        $jym = Tenant::query()->firstOrCreate([
            'razon_social' => 'Vidrios y Estructuras JyM',
        ]);

        $casaAngel = Tenant::query()->firstOrCreate([
            'razon_social' => 'Casa Angel',
        ]);

        foreach ($this->jymGalleryItems() as $index => $item) {
            $categoria = JymCategoria::query()->firstOrCreate(
                ['nombre' => $item['category']],
                ['descripcion' => $item['category']],
            );

            $proyecto = Proyecto::query()->firstOrCreate(
                ['nombre' => $item['name']],
                [
                    'categoria_id' => $categoria->id,
                    'descripcion' => $item['description'],
                ],
            );

            $this->storeVitrina($jym, [
                'source_model' => $proyecto,
                'nombre' => $item['name'],
                'descripcion' => $item['description'],
                'media_type' => 'image',
                'preview_url' => $item['preview'] ?? $item['img'],
                'detail_url' => $item['detail'] ?? $item['img'],
                'categoria' => $item['category'],
                'grupo' => $item['group'] ?? null,
                'nivel' => random_int(1, 5),
            ]);
        }

        foreach ($this->casaAngelGalleryItems() as $index => $item) {
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
                    'ocasion_id' => $ocasion->id,
                    'tematica_id' => $tematica->id,
                    'descripcion' => 'Proyecto: '.$projectName,
                ],
            );

            $this->storeVitrina($casaAngel, [
                'source_model' => $evento,
                'nombre' => Str::title($alt),
                'descripcion' => 'Proyecto: '.$projectName.'. Ocasión: '.Str::title((string) $item['ocasion']).'. Temática: '.Str::title((string) $item['tematica']).'.',
                'media_type' => 'image',
                'preview_url' => $imagePath !== '' ? $imagePath : null,
                'detail_url' => $imagePath !== '' ? $imagePath : null,
                'categoria' => Str::title((string) $item['ocasion']),
                'grupo' => Str::title((string) $item['tematica']),
                'nivel' => $index + 1,
            ]);
        }
    }

    private function storeVitrina(Tenant $tenant, array $payload): void
    {
        $vitrina = Vitrina::query()->firstOrNew([
            'tenant_id' => $tenant->id,
            'nombre' => $payload['nombre'],
        ]);

        $vitrina->fill([
            'descripcion' => $payload['descripcion'],
        ]);

        $vitrina->tenant()->associate($tenant);
        $vitrina->save();

        $this->syncMultimedia($vitrina, $payload);

        if (! empty($payload['categoria'])) {
            $vitrina->categoria()->updateOrCreate([], [
                'nombre' => $payload['categoria'],
                'descripcion' => $payload['categoria'],
            ]);
        }

        if (! empty($payload['grupo'])) {
            $vitrina->grupo()->updateOrCreate([], [
                'nombre' => $payload['grupo'],
                'descripcion' => $payload['grupo'],
            ]);
        }

        $vitrina->nivel()->updateOrCreate([], [
            'nivel' => $payload['nivel'],
        ]);
    }

    private function syncMultimedia(Vitrina $vitrina, array $payload): void
    {
        $detailUrl = $payload['detail_url'] ?? $payload['preview_url'] ?? null;
        $previewUrl = $payload['preview_url'] ?? $detailUrl;

        if (! $detailUrl && ! $previewUrl) {
            $vitrina->multimedias()->detach();

            return;
        }

        $multimedia = Multimedia::query()->firstOrCreate(
            [
                'url' => $detailUrl ?? $previewUrl,
                'preview_url' => $previewUrl,
                'type' => $payload['media_type'] ?? 'image',
            ],
            [
                'mime_type' => $this->guessMimeType((string) ($detailUrl ?? $previewUrl)),
            ],
        );

        $sourceModel = $payload['source_model'] ?? null;

        $vitrina->multimedias()->sync([
            $multimedia->id => [
                'source_type' => $sourceModel ? $sourceModel::class : null,
                'source_id' => $sourceModel?->getKey(),
                'source_connection' => $sourceModel?->getConnectionName(),
                'orden' => 0,
                'es_portada' => true,
            ],
        ]);
    }

    private function casaAngelGalleryItems(): array
    {
        $json = file_get_contents(resource_path('utils/galeria.json'));

        return json_decode($json ?: '[]', true, 512, JSON_THROW_ON_ERROR);
    }

    private function decodeProject(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded !== false ? Str::title(str_replace(['_', '-'], ' ', $decoded)) : Str::title($value);
    }

    private function guessMimeType(string $path): ?string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }

    private function jymGalleryItems(): array
    {
        return [
            ['img' => '/IMG/Fotos/16-9h/Pulido027.jpg', 'category' => 'Baños y Adecuaciones', 'name' => 'Baño Moderno con Vidrio Templado', 'description' => 'Instalación de vidrio templado en baño residencial con herrajes de alta calidad.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido069.jpg', 'category' => 'Oficinas', 'name' => 'División de Oficina Ejecutiva', 'description' => 'Separadores de vidrio para crear espacios privados en oficinas corporativas.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido062.jpg', 'category' => 'Divisiones', 'name' => 'Muro Divisorio Transparente', 'description' => 'Muro divisorio de vidrio laminado para espacios comerciales.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido094.jpg', 'category' => 'Texturizado', 'name' => 'Vidrio Texturizado Decorativo', 'description' => 'Aplicación de texturizado en vidrio para efectos visuales únicos.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido098.jpg', 'category' => 'Oficinas', 'name' => 'Recepción Corporativa', 'description' => 'Diseño de recepción con elementos de vidrio y estructuras metálicas.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido121.jpg', 'category' => 'Interiores', 'name' => 'Decoración Interior Moderna', 'description' => 'Elementos decorativos de vidrio en espacios interiores residenciales.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido131.jpg', 'category' => 'Divisiones', 'name' => 'Separador de Ambientes', 'description' => 'Separador de ambientes con vidrio templado y marco minimalista.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido140.jpg', 'category' => 'Locativos', 'name' => 'Local Comercial Vidriado', 'description' => 'Fachada y vidriado completo para local comercial.'],
            ['img' => '/IMG/Fotos/1-1/Pulido024.jpg', 'category' => 'Portafolio General', 'name' => 'Proyecto Residencial', 'description' => 'Instalación completa de vidrios en proyecto residencial.'],
            ['img' => '/IMG/Fotos/1-1/Pulido034.jpg', 'category' => 'Portafolio General', 'name' => 'Estructura Metálica', 'description' => 'Estructura metálica con vidrios para construcción moderna.'],
            ['img' => '/IMG/Fotos/1-1/Pulido048.jpg', 'category' => 'Portafolio General', 'name' => 'Vidrio Laminado', 'description' => 'Aplicación de vidrio laminado en edificaciones.'],
            ['img' => '/IMG/Fotos/1-1/Pulido051.jpg', 'category' => 'Portafolio General', 'name' => 'Instalación Profesional', 'description' => 'Trabajo profesional de instalación de vidrios y estructuras.'],
            ['img' => '/IMG/Fotos/1-1/Pulido056.jpg', 'category' => 'Portafolio General', 'name' => 'Diseño Arquitectónico', 'description' => 'Integración de vidrio en diseños arquitectónicos innovadores.'],
            ['img' => '/IMG/Fotos/1-1/Pulido061.jpg', 'category' => 'Portafolio General', 'name' => 'Proyecto Empresarial', 'description' => 'Desarrollo de proyecto empresarial con elementos de vidrio.'],
        ];
    }
}
