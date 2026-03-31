<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Vitrina;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VitrinaSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $jym = Tenant::query()->firstOrCreate([
            'razon_social' => 'Vidrios y Estructuras JyM',
        ]);

        $casaAngel = Tenant::query()->firstOrCreate([
            'razon_social' => 'Casa Angel',
        ]);

        foreach ($this->jymGalleryItems() as $index => $item) {
            $this->storeVitrina($jym, [
                'nombre' => $item['name'],
                'descripcion' => $item['description'],
           
                'categoria' => $item['category'],
                'grupo' => $item['group'] ?? null,
                'nivel' => random_int(1, 5),
            ]);

         
        }

        foreach ($this->casaAngelGalleryItems() as $index => $item) {
            $projectName = $this->decodeProject($item['proyecto']);
            $alt = trim((string) ($item['alt'] ?? 'Vitrina Casa Angel'));

            $this->storeVitrina($casaAngel, [
                'nombre' => Str::title($alt),
                'descripcion' => 'Proyecto: '.$projectName.'. Ocasión: '.Str::title((string) $item['ocasion']).'. Temática: '.Str::title((string) $item['tematica']).'.',
                
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
            'imagen' => $payload['imagen'],
        ]);

        $vitrina->tenant()->associate($tenant);
        $vitrina->save();

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

    private function multimediaStore(): array
    {
        Multimedia::query()->firstOrCreate([
            'url' => $path,
            'type' => 'imagen',
            'mime_type' => 'image/jpeg',
        ]);

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
