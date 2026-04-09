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

            $groupName = $item['group'] ?? $this->inferGroupName($item);

            $grupo = JymGrupo::query()->firstOrCreate(
                ['nombre' => $groupName],
                ['descripcion' => $groupName],
            );

            $proyecto = Proyecto::query()->firstOrCreate(
                ['nombre' => $item['name']],
                [
                    'categoria_id' => $categoria->id,
                    'grupo_id' => $grupo->id,
                    'descripcion' => $item['description'],
                    'materiales' => $this->inferMateriales(implode(' ', array_filter([$groupName, $item['name'], $item['description']]))),
                    'publicar_en_vitrina' => false,
                ],
            );

            $proyecto->forceFill([
                'grupo_id' => $proyecto->grupo_id ?: $grupo->id,
                'materiales' => $proyecto->materiales ?: $this->inferMateriales(implode(' ', array_filter([$groupName, $item['name'], $item['description']]))),
            ])->save();

            $multimedia = Multimedia::query()->firstOrCreate(
                [
                    'url' => $item['detail'] ?? $item['img'],
                    'preview_url' => $item['preview'] ?? $item['img'],
                    'type' => 'image',
                ],
                [
                    'mime_type' => $this->guessMimeType($item['detail'] ?? $item['img']),
                    'alt' => sprintf('Proyecto %s foto %s', $item['name'], basename((string) ($item['detail'] ?? $item['img']))),
                    'orientacion' => $this->inferOrientation((string) ($item['detail'] ?? $item['img'])),
                    'nivel' => 0,
                ],
            );

            $multimedia->forceFill([
                'alt' => $multimedia->alt ?: sprintf('Proyecto %s foto %s', $item['name'], basename((string) ($item['detail'] ?? $item['img']))),
                'orientacion' => $multimedia->orientacion ?: $this->inferOrientation((string) ($item['detail'] ?? $item['img'])),
                'nivel' => max(0, (int) ($multimedia->nivel ?? 0)),
            ])->save();

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

    private function inferGroupName(array $item): string
    {
        return trim((string) ($item['name'] ?? 'General')) ?: 'General';
    }

    private function inferOrientation(string $path): string
    {
        $normalized = Str::lower($path);

        if (Str::contains($normalized, '/1-1/')) {
            return 'cuadrada';
        }

        if (Str::contains($normalized, ['9-16', 'vertical'])) {
            return 'vertical';
        }

        return 'horizontal';
    }

    private function inferMateriales(string $text): array
    {
        $haystack = Str::lower($text);
        $materiales = [];

        foreach ([
            'Acero inoxidable' => ['acero inoxidable', 'acero'],
            'Herrajes' => ['herraje', 'herrajes'],
            'Vidrio Templado' => ['vidrio templado', 'vidrio'],
            'Vidrio Laminado' => ['vidrio laminado'],
            'Aluminio' => ['aluminio'],
            'Estructura Metalica' => ['metalica', 'metálica', 'metal'],
        ] as $material => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($haystack, $keyword)) {
                    $materiales[] = $material;
                    break;
                }
            }
        }

        return array_values(array_unique($materiales !== [] ? $materiales : ['Vidrio Templado']));
    }

    private function galleryItems(): array
    {
        return [
            "proyectos" => [
                ['img' => '/IMG/Fotos/16-9h/Pulido002.jpg', 'category' => 'Vidrios', 'name' => 'Fachada Comercial', 'description' => 'Fachada comercial con vidrios de alta resistencia y diseño moderno.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido027.jpg', 'category' => 'Baños y Adecuaciones', 'name' => 'Baño Moderno con Vidrio Templado', 'description' => 'Instalación de vidrio templado en baño residencial con herrajes de alta calidad.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido069.jpg', 'category' => 'Oficinas', 'name' => 'División de Oficina', 'description' => 'Separadores de vidrio para crear espacios privados en oficinas corporativas.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido062.jpg', 'category' => 'Oficinas', 'name' => 'División de Oficina', 'description' => 'Muro divisorio de vidrio laminado para espacios comerciales.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido094.jpg', 'category' => 'Texturizado', 'name' => 'Vidrio Texturizado', 'description' => 'Aplicación de texturizado en vidrio para efectos visuales únicos.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido098.jpg', 'category' => 'Oficinas', 'name' => 'Recepción y modulos de atencion', 'description' => 'Diseño de recepción con elementos de vidrio y estructuras metálicas.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido121.jpg', 'category' => 'Oficinas', 'name' => 'Recepción y modulos de atencion', 'description' => 'Elementos decorativos de vidrio en espacios interiores residenciales.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido131.jpg', 'category' => 'Oficinas', 'name' => 'Separador de Ambientes', 'description' => 'Separador de ambientes con vidrio templado y marco minimalista.'],
            ['img' => '/IMG/Fotos/16-9h/Pulido140.jpg', 'category' => 'Locativos', 'name' => 'Local Comercial', 'description' => 'Fachada y vidriado completo para local comercial.'],
            ['img' => '/IMG/Fotos/1-1/Pulido024.jpg', 'category' => 'Vidrios', 'name' => 'Proyecto Residencial', 'description' => 'Instalación completa de vidrios en proyecto residencial.'],
            ['img' => '/IMG/Fotos/1-1/Pulido034.jpg', 'category' => 'Vidrios', 'name' => 'Estructura Metálica', 'description' => 'Estructura metálica con vidrios para construcción moderna.'],
            ['img' => '/IMG/Fotos/1-1/Pulido048.jpg', 'category' => 'Vidrios', 'name' => 'Vidrio Laminado', 'description' => 'Aplicación de vidrio laminado en edificaciones.'],
            ['img' => '/IMG/Fotos/1-1/Pulido051.jpg', 'category' => 'Vidrios', 'name' => 'Instalación Profesional', 'description' => 'Trabajo profesional de instalación de vidrios y estructuras.'],
            ['img' => '/IMG/Fotos/1-1/Pulido056.jpg', 'category' => 'Vidrios', 'name' => 'Diseño Arquitectónico', 'description' => 'Integración de vidrio en diseños arquitectónicos innovadores.'],
            ['img' => '/IMG/Fotos/1-1/Pulido061.jpg', 'category' => 'Vidrios', 'name' => 'Proyecto Empresarial', 'description' => 'Desarrollo de proyecto empresarial con elementos de vidrio.'],
            
        ],
        "materiales" => [
            ['name'=>'Vidrio Crudo', 'img'=>"/IMG/materiales/Vidrio_Crudo.jpg", 'description' => 'Vidrio sin tratamiento, utilizado para aplicaciones básicas y económicas.'],
            ['name' => 'Acero Inoxidable', 'img'=>"/IMG/materiales/Acero_inoxidable.jpg", 'description' => 'Material resistente a la corrosión, ideal para estructuras y herrajes.'],
            ['name' => 'Herrajes', 'img'=>"", 'description' => 'Componentes metálicos utilizados para fijar y soportar elementos de vidrio.'],
            ['name' => 'Vidrio Templado', 'img'=>"", 'description' => 'Vidrio tratado térmicamente para mayor resistencia y seguridad.'],
            ['name' => 'Vidrio Laminado', 'img'=>"", 'description' => 'Vidrio compuesto por capas unidas con una película intermedia para mayor seguridad.'],
            ['name' => 'Aluminio', 'img'=>"", 'description' => 'Material ligero y resistente, utilizado en marcos y estructuras de soporte.'],
            ['name' => 'Estructura Metálica', 'img'=>"", 'description' => 'Sistemas de soporte metálicos para proyectos de construcción con vidrio.'],
        ],
        ];
    }
}
