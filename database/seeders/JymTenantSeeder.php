<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JymCategoria;
use App\Models\JymGrupo;
use App\Models\Multimedia;
use App\Models\Proyecto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JymTenantSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->galleryItems() as $item) {
            $categoria = JymCategoria::query()->firstOrCreate(
                ['nombre' => $item['category']],
                ['descripcion' => $item['category']],
            );

            $grupo = JymGrupo::query()->firstOrCreate(
                ['nombre' => $item['group'] ?? 'General'],
                ['descripcion' => $item['group'] ?? 'General'],
            );

            $proyecto = Proyecto::query()->firstOrCreate(
                ['nombre' => $item['name']],
                [
                    'categoria_id' => $categoria->id,
                    'grupo_id' => $grupo->id,
                    'descripcion' => $item['description'],
                    'publicar_en_vitrina' => false,
                ],
            );

            $multimedia = Multimedia::query()->firstOrCreate(
                [
                    'url' => $item['detail'] ?? $item['img'],
                    'preview_url' => $item['preview'] ?? $item['img'],
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($item['detail'] ?? $item['img']),
                ],
            );

            $proyecto->multimedias()->syncWithoutDetaching([$multimedia->id]);
        }
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

    private function galleryItems(): array
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
