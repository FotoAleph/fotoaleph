<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\JymCategoria;
use App\Models\JymGrupo;
use App\Models\Multimedia;
use App\Models\Proyecto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JymTenantSeeder extends Seeder
{
    public function run(): void
    {
        $hasProyectoMateriales = Schema::connection('tenant_jym')->hasColumn('proyectos', 'materiales');
        $hasMultimediaOrientacion = Schema::connection('tenant_jym')->hasColumn('multimedia', 'orientacion');

        foreach ($this->projectItems() as $item) {
            $categoria = JymCategoria::query()->firstOrCreate(
                ['nombre' => $item['category']],
                ['descripcion' => $item['category']],
            );

            $groupName = $item['group'] ?? $this->inferGroupName($item);

            $grupo = JymGrupo::query()->firstOrCreate(
                ['nombre' => $groupName],
                ['descripcion' => $groupName],
            );

            $projectPayload = [
                'categoria_id' => $categoria->id,
                'grupo_id' => $grupo->id,
                'descripcion' => $item['description'],

            ];

            if ($hasProyectoMateriales) {
                $projectPayload['materiales'] = $this->inferMateriales(implode(' ', array_filter([$groupName, $item['name'], $item['description']])));
            }

            $proyecto = Proyecto::query()->firstOrCreate(
                ['nombre' => $item['name']],
                $projectPayload,
            );

            $projectUpdatePayload = [
                'grupo_id' => $proyecto->grupo_id ?: $grupo->id,
            ];

            if ($hasProyectoMateriales) {
                $projectUpdatePayload['materiales'] = $proyecto->materiales ?: $this->inferMateriales(implode(' ', array_filter([$groupName, $item['name'], $item['description']])));
            }

            $proyecto->forceFill($projectUpdatePayload)->save();

            foreach ($item['media'] as $mediaItem) {
                $detailUrl = $mediaItem['detail'] ?? $mediaItem['img'];
                $previewUrl = $mediaItem['preview'] ?? $mediaItem['img'] ?? $detailUrl;
                $mediaType = $this->guessMediaType($detailUrl);

                $mediaPayload = [
                    'mime_type' => $this->guessMimeType($detailUrl),
                    'alt' => sprintf('Proyecto %s archivo %s', $item['name'], basename((string) $detailUrl)),
                    'nivel' => 0,
                ];

                if ($hasMultimediaOrientacion) {
                    $mediaPayload['orientacion'] = $this->inferOrientation((string) $detailUrl);
                }

                $multimedia = Multimedia::on('tenant_jym')->firstOrCreate(
                    [
                        'url' => $detailUrl,
                        'preview_url' => $previewUrl,
                        'type' => $mediaType,
                    ],
                    $mediaPayload,
                );

                $mediaUpdatePayload = [
                    'alt' => $multimedia->alt ?: sprintf('Proyecto %s archivo %s', $item['name'], basename((string) $detailUrl)),
                    'nivel' => max(0, (int) ($multimedia->nivel ?? 0)),
                ];


                $multimedia->forceFill($mediaUpdatePayload)->save();

                $proyecto->multimedias()->syncWithoutDetaching([$multimedia->id]);
            }
        }
    }

    private function guessMediaType(string $path): string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'mp4' => 'video',
            'webm' => 'video',
            'ogg' => 'video',
            default => 'image',
        };
    }

    private function guessMimeType(string $path): string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            default => 'image/jpeg',
        };
    }

    private function inferGroupName(array $item): string
    {
        $groupName = trim((string) ($item['group'] ?? $item['grupo'] ?? $item['description'] ?? $item['descripcion'] ?? $item['name'] ?? $item['nombre'] ?? ''));

        return $groupName !== '' ? Str::title($groupName) : 'General';
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

    private function projectItems(): array
    {
        $items = $this->galleryItems();

        if (isset($items['proyectos']) && is_array($items['proyectos'])) {
            return $this->normalizeLegacyProjects($items['proyectos']);
        }

        if (! is_array($items)) {
            return [];
        }

        $projects = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized = $this->normalizeCurrentProjectItem($item);

            if ($normalized !== null) {
                $projects[] = $normalized;
            }
        }

        return $projects;
    }

    private function normalizeLegacyProjects(array $items): array
    {
        $groupedProjects = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $projectKey = trim((string) ($item['proyecto'] ?? $item['project'] ?? ''));
            $projectKey = $projectKey !== '' ? $projectKey : md5(json_encode($item));
            $groupedProjects[$projectKey][] = $item;
        }

        $projects = [];

        foreach ($groupedProjects as $projectKey => $projectItems) {
            $normalized = $this->normalizeLegacyProjectItem((string) $projectKey, $projectItems);

            if ($normalized !== null) {
                $projects[] = $normalized;
            }
        }

        return $projects;
    }

    private function normalizeLegacyProjectItem(string $projectKey, array $items): ?array
    {
        $media = [];

        foreach ($items as $item) {
            $normalizedMedia = $this->normalizeMediaItem($item);

            if ($normalizedMedia !== null) {
                $media[] = $normalizedMedia;
            }
        }

        if ($media === []) {
            return null;
        }

        $description = $this->firstNonEmpty(array_map(
            fn (array $item): string => trim((string) ($item['descripcion'] ?? $item['description'] ?? '')),
            $items,
        ));

        $groupName = $this->firstNonEmpty(array_map(
            fn (array $item): string => trim((string) ($item['grupo'] ?? $item['group'] ?? '')),
            $items,
        ), $projectKey !== '' ? 'Proyecto '.$projectKey : 'General');

        $projectName = $description !== ''
            ? 'Proyecto '.$projectKey.' - '.Str::title($description)
            : 'Proyecto '.$projectKey;

        return [
            'name' => trim($projectName),
            'description' => $description,
            'category' => $this->inferCategoryName($description, $groupName),
            'group' => $groupName,
            'media' => $media,
        ];
    }

    private function normalizeCurrentProjectItem(array $item): ?array
    {
        $media = $this->normalizeMediaItem($item);

        if ($media === null) {
            return null;
        }

        $description = trim((string) ($item['description'] ?? $item['descripcion'] ?? ''));
        $name = trim((string) ($item['name'] ?? $item['nombre'] ?? basename((string) $media['detail'])));
        $category = trim((string) ($item['category'] ?? $item['categoria'] ?? $this->inferCategoryName($description, $name)));
        $group = trim((string) ($item['group'] ?? $item['grupo'] ?? $this->inferGroupName($item)));

        return [
            'name' => $name !== '' ? $name : basename((string) $media['detail']),
            'description' => $description,
            'category' => $category !== '' ? $category : 'Portafolio General',
            'group' => $group !== '' ? $group : 'General',
            'media' => [$media],
        ];
    }

    private function normalizeMediaItem(array $item): ?array
    {
        $detailUrl = trim((string) ($item['detail'] ?? $item['img'] ?? $item['url'] ?? ''));

        if ($detailUrl === '') {
            return null;
        }

        $previewUrl = trim((string) ($item['preview'] ?? $item['img'] ?? $item['url'] ?? $detailUrl));

        return [
            'detail' => $detailUrl,
            'preview' => $previewUrl !== '' ? $previewUrl : $detailUrl,
            'img' => $previewUrl !== '' ? $previewUrl : $detailUrl,
        ];
    }

    private function inferCategoryName(string $description, string $fallback = ''): string
    {
        $haystack = Str::lower(trim($description.' '.$fallback));

        foreach ([
            'Baños y Adecuaciones' => ['baño', 'bano'],
            'Oficinas' => ['oficina', 'recepcion', 'recepción', 'corporativa'],
            'Divisiones' => ['division', 'división', 'separador'],
            'Texturizado' => ['textur'],
            'Interiores' => ['interior', 'espejo', 'decorativa', 'decorativo'],
            'Locativos' => ['local', 'fachada', 'marquesina', 'ventaneria', 'ventanería', 'puerta', 'baranda', 'escalera'],
        ] as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (Str::contains($haystack, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Portafolio General';
    }

    private function firstNonEmpty(array $values, string $fallback = ''): string
    {
        foreach ($values as $value) {
            $normalized = trim((string) $value);

            if ($normalized !== '') {
                return $normalized;
            }
        }

        return $fallback;
    }

    private function galleryItems(): array
    {
        return [
            "proyectos" => [
             array('url' => '/IMG/proyectos/3/P03000.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03000.jpg'),
  array('url' => '/IMG/proyectos/3/P03001.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03001.jpg'),
  array('url' => '/IMG/proyectos/3/P03002.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03002.jpg'),
  array('url' => '/IMG/proyectos/3/P03003.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03003.jpg'),
  array('url' => '/IMG/proyectos/3/P03004.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03004.jpg'),
  array('url' => '/IMG/proyectos/3/P03005.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03005.jpg'),
  array('url' => '/IMG/proyectos/3/P03006.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03006.jpg'),
  array('url' => '/IMG/proyectos/3/P03007.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03007.jpg'),
  array('url' => '/IMG/proyectos/3/P03008.jpg','proyecto' => '3','descripcion' => '','nombre' => 'P03008.jpg'),
  array('url' => '/IMG/proyectos/4/P04009.jpg','proyecto' => '4','descripcion' => '','nombre' => 'P04009.jpg'),
  array('url' => '/IMG/proyectos/4/P04010.jpg','proyecto' => '4','descripcion' => '','nombre' => 'P04010.jpg'),
  array('url' => '/IMG/proyectos/4/P04011.jpg','proyecto' => '4','descripcion' => '','nombre' => 'P04011.jpg'),
  array('url' => '/IMG/proyectos/5/P05012.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05012.jpg'),
  array('url' => '/IMG/proyectos/5/P05013.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05013.jpg'),
  array('url' => '/IMG/proyectos/5/P05014.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05014.jpg'),
  array('url' => '/IMG/proyectos/5/P05015.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05015.jpg'),
  array('url' => '/IMG/proyectos/5/P05016.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05016.jpg'),
  array('url' => '/IMG/proyectos/5/P05017.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05017.jpg'),
  array('url' => '/IMG/proyectos/5/P05018.jpg','proyecto' => '5','descripcion' => '','nombre' => 'P05018.jpg'),
  array('url' => '/IMG/proyectos/6/P06000.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06000.jpeg'),
  array('url' => '/IMG/proyectos/6/P06001.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06001.jpeg'),
  array('url' => '/IMG/proyectos/6/P06002.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06002.jpeg'),
  array('url' => '/IMG/proyectos/6/P06003.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06003.jpeg'),
  array('url' => '/IMG/proyectos/6/P06004.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06004.jpeg'),
  array('url' => '/IMG/proyectos/6/P06005.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06005.jpeg'),
  array('url' => '/IMG/proyectos/6/P06006.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06006.jpeg'),
  array('url' => '/IMG/proyectos/6/P06007.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06007.jpeg'),
  array('url' => '/IMG/proyectos/6/P06008.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06008.jpeg'),
  array('url' => '/IMG/proyectos/6/P06009.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06009.jpeg'),
  array('url' => '/IMG/proyectos/6/P06010.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06010.jpeg'),
  array('url' => '/IMG/proyectos/6/P06011.jpeg','proyecto' => '6','descripcion' => '','nombre' => 'P06011.jpeg'),
  array('url' => '/IMG/proyectos/7/P07000.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07000.jpeg'),
  array('url' => '/IMG/proyectos/7/P07001.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07001.jpeg'),
  array('url' => '/IMG/proyectos/7/P07002.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07002.jpeg'),
  array('url' => '/IMG/proyectos/7/P07003.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07003.jpeg'),
  array('url' => '/IMG/proyectos/7/P07004.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07004.jpeg'),
  array('url' => '/IMG/proyectos/7/P07005.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07005.jpeg'),
  array('url' => '/IMG/proyectos/7/P07006.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07006.jpeg'),
  array('url' => '/IMG/proyectos/7/P07007.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07007.jpeg'),
  array('url' => '/IMG/proyectos/7/P07008.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07008.jpeg'),
  array('url' => '/IMG/proyectos/7/P07009.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07009.jpeg'),
  array('url' => '/IMG/proyectos/7/P07010.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07010.jpeg'),
  array('url' => '/IMG/proyectos/7/P07011.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07011.jpeg'),
  array('url' => '/IMG/proyectos/7/P07012.jpeg','proyecto' => '7','descripcion' => '','nombre' => 'P07012.jpeg'),
  array('url' => '/IMG/proyectos/8/P08000.jpeg','proyecto' => '8','descripcion' => '','nombre' => 'P08000.jpeg'),
  array('url' => '/IMG/proyectos/8/P08001.jpeg','proyecto' => '8','descripcion' => '','nombre' => 'P08001.jpeg'),
  array('url' => '/IMG/proyectos/8/P08002.jpeg','proyecto' => '8','descripcion' => '','nombre' => 'P08002.jpeg'),
  array('url' => '/IMG/proyectos/8/P08003.jpeg','proyecto' => '8','descripcion' => '','nombre' => 'P08003.jpeg'),
  array('url' => '/IMG/proyectos/8/P08004.jpeg','proyecto' => '8','descripcion' => '','nombre' => 'P08004.jpeg'),
  array('url' => '/IMG/proyectos/8/P08005.mp4','proyecto' => '8','descripcion' => '','nombre' => 'P08005.mp4'),
  array('url' => '/IMG/proyectos/9/P9009.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9009.jpeg'),
  array('url' => '/IMG/proyectos/9/P9010.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9010.jpeg'),
  array('url' => '/IMG/proyectos/9/P9011.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9011.jpeg'),
  array('url' => '/IMG/proyectos/9/P9012.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9012.jpeg'),
  array('url' => '/IMG/proyectos/9/P9013.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9013.jpeg'),
  array('url' => '/IMG/proyectos/9/P9014.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9014.jpeg'),
  array('url' => '/IMG/proyectos/9/P9015.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9015.jpeg'),
  array('url' => '/IMG/proyectos/9/P9016.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9016.jpeg'),
  array('url' => '/IMG/proyectos/9/P9017.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9017.jpeg'),
  array('url' => '/IMG/proyectos/9/P9018.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9018.jpeg'),
  array('url' => '/IMG/proyectos/9/P9019.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9019.jpeg'),
  array('url' => '/IMG/proyectos/9/P9020.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9020.jpeg'),
  array('url' => '/IMG/proyectos/9/P9021.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9021.jpeg'),
  array('url' => '/IMG/proyectos/9/P9022.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9022.jpeg'),
  array('url' => '/IMG/proyectos/9/P9023.jpeg','proyecto' => '9','descripcion' => '','nombre' => 'P9023.jpeg'),
  array('url' => '/IMG/proyectos/10/P10000.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10000.jpeg'),
  array('url' => '/IMG/proyectos/10/P10001.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10001.jpeg'),
  array('url' => '/IMG/proyectos/10/P10002.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10002.jpeg'),
  array('url' => '/IMG/proyectos/10/P10003.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10003.jpeg'),
  array('url' => '/IMG/proyectos/10/P10004.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10004.jpeg'),
  array('url' => '/IMG/proyectos/10/P10005.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10005.jpeg'),
  array('url' => '/IMG/proyectos/10/P10006.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10006.jpeg'),
  array('url' => '/IMG/proyectos/10/P10007.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10007.jpeg'),
  array('url' => '/IMG/proyectos/10/P10008.jpeg','proyecto' => '10','descripcion' => '','nombre' => 'P10008.jpeg'),
  array('url' => '/IMG/proyectos/11/P11000.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11000.jpeg'),
  array('url' => '/IMG/proyectos/11/P11001.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11001.jpeg'),
  array('url' => '/IMG/proyectos/11/P11002.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11002.jpeg'),
  array('url' => '/IMG/proyectos/11/P11003.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11003.jpeg'),
  array('url' => '/IMG/proyectos/11/P11004.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11004.jpeg'),
  array('url' => '/IMG/proyectos/11/P11005.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11005.jpeg'),
  array('url' => '/IMG/proyectos/11/P11006.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11006.jpeg'),
  array('url' => '/IMG/proyectos/11/P11007.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11007.jpeg'),
  array('url' => '/IMG/proyectos/11/P11008.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11008.jpeg'),
  array('url' => '/IMG/proyectos/11/P11009.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11009.jpeg'),
  array('url' => '/IMG/proyectos/11/P11010.jpeg','proyecto' => '11','descripcion' => 'Escalera En Acero Inoxidable','nombre' => 'P11010.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido200.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido200.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido201.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido201.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido202.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido202.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido203.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido203.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido204.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido204.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido205.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido205.jpeg'),
  array('url' => '/IMG/proyectos/12/Pulido206.jpeg','proyecto' => '12','descripcion' => '','nombre' => 'Pulido206.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido032.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido032.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido033.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido033.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido034.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido034.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido035.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido035.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido036.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido036.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido037.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido037.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido038.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido038.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido039.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido039.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido040.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido040.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido041.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido041.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido042.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido042.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido043.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido043.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido044.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido044.jpeg'),
  array('url' => '/IMG/proyectos/13/Pulido045.jpeg','proyecto' => '13','descripcion' => '','nombre' => 'Pulido045.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido207.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido207.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido208.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido208.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido209.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido209.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido210.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido210.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido211.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido211.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido212.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido212.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido213.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido213.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido214.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido214.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido215.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido215.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido216.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido216.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido217.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido217.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido218.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido218.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido219.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido219.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido220.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido220.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido221.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido221.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido222.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido222.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido223.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido223.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido224.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido224.jpeg'),
  array('url' => '/IMG/proyectos/14/Pulido225.jpeg','proyecto' => '14','descripcion' => '','nombre' => 'Pulido225.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido024.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido024.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido025.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido025.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido026.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido026.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido027.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido027.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido028.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido028.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido029.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido029.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido030.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido030.jpeg'),
  array('url' => '/IMG/proyectos/15/Pulido031.jpeg','proyecto' => '15','descripcion' => '','nombre' => 'Pulido031.jpeg'),
  array('url' => '/IMG/proyectos/16/Pulido046.jpeg','proyecto' => '16','descripcion' => '','nombre' => 'Pulido046.jpeg'),
  array('url' => '/IMG/proyectos/16/Pulido047.jpeg','proyecto' => '16','descripcion' => '','nombre' => 'Pulido047.jpeg'),
  array('url' => '/IMG/proyectos/16/Pulido048.jpeg','proyecto' => '16','descripcion' => '','nombre' => 'Pulido048.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido049.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido049.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido050.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido050.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido051.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido051.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido052.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido052.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido053.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido053.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido054.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido054.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido055.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido055.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido056.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido056.jpeg'),
  array('url' => '/IMG/proyectos/17/Pulido057.jpeg','proyecto' => '17','descripcion' => '','nombre' => 'Pulido057.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido058.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido058.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido059.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido059.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido060.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido060.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido061.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido061.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido062.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido062.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido063.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido063.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido064.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido064.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido065.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido065.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido066.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido066.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido067.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido067.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido068.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido068.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido069.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido069.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido070.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido070.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido071.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido071.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido072.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido072.jpeg'),
  array('url' => '/IMG/proyectos/18/Pulido073.jpeg','proyecto' => '18','descripcion' => '','nombre' => 'Pulido073.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido074.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido074.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido075.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido075.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido076.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido076.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido077.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido077.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido078.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido078.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido079.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido079.jpeg'),
  array('url' => '/IMG/proyectos/19/Pulido080.jpeg','proyecto' => '19','descripcion' => '','nombre' => 'Pulido080.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido081.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido081.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido082.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido082.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido083.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido083.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido084.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido084.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido085.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido085.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido086.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido086.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido087.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido087.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido088.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido088.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido089.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido089.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido090.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido090.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido091.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido091.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido092.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido092.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido093.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido093.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido094.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido094.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido095.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido095.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido096.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido096.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido097.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido097.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido098.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido098.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido099.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido099.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido100.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido100.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido101.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido101.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido102.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido102.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido103.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido103.jpeg'),
  array('url' => '/IMG/proyectos/20/Pulido104.jpeg','proyecto' => '20','descripcion' => '','nombre' => 'Pulido104.jpeg'),
  array('url' => '/IMG/proyectos/21/Pulido105.jpeg','proyecto' => '21','descripcion' => '','nombre' => 'Pulido105.jpeg'),
  array('url' => '/IMG/proyectos/21/Pulido106.jpeg','proyecto' => '21','descripcion' => '','nombre' => 'Pulido106.jpeg'),
  array('url' => '/IMG/proyectos/21/video.mp4','proyecto' => '21','descripcion' => '','nombre' => 'video.mp4'),
  array('url' => '/IMG/proyectos/22/Pulido107.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido107.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido108.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido108.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido109.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido109.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido110.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido110.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido111.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido111.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido112.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido112.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido113.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido113.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido114.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido114.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido115.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido115.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido116.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido116.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido117.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido117.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido118.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido118.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido119.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido119.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido120.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido120.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido121.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido121.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido122.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido122.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido123.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido123.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido124.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido124.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido125.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido125.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido126.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido126.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido127.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido127.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido128.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido128.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido129.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido129.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido130.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido130.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido131.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido131.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido132.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido132.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido133.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido133.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido134.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido134.jpeg'),
  array('url' => '/IMG/proyectos/22/Pulido135.jpeg','proyecto' => '22','descripcion' => '','nombre' => 'Pulido135.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido136.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido136.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido137.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido137.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido138.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido138.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido139.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido139.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido140.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido140.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido141.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido141.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido142.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido142.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido143.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido143.jpeg'),
  array('url' => '/IMG/proyectos/23/Pulido144.jpeg','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'Pulido144.jpeg'),
  array('url' => '/IMG/proyectos/23/video.mp4','proyecto' => '23','descripcion' => 'Fachadas colgantes en acero inoxidable','nombre' => 'video.mp4'),
  array('url' => '/IMG/proyectos/24/Pulido145.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido145.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido146.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido146.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido147.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido147.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido148.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido148.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido149.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido149.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido150.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido150.jpeg'),
  array('url' => '/IMG/proyectos/24/Pulido151.jpeg','proyecto' => '24','descripcion' => '','nombre' => 'Pulido151.jpeg'),
  array('url' => '/IMG/proyectos/24/video.mp4','proyecto' => '24','descripcion' => '','nombre' => 'video.mp4'),
  array('url' => '/IMG/proyectos/25/Pulido152.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido152.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido153.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido153.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido154.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido154.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido155.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido155.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido156.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido156.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido157.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido157.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido158.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido158.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido159.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido159.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido160.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido160.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido161.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido161.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido162.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido162.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido163.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido163.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido164.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido164.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido165.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido165.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido166.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido166.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido167.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido167.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido168.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido168.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido169.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido169.jpeg'),
  array('url' => '/IMG/proyectos/25/Pulido170.jpeg','proyecto' => '25','descripcion' => 'Estructura en hierro y vidrio laminado; vidrio incoloro y vidrio espejo','nombre' => 'Pulido170.jpeg'),
  array('url' => '/IMG/proyectos/26/Pulido171.jpeg','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'Pulido171.jpeg'),
  array('url' => '/IMG/proyectos/26/Pulido172.jpeg','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'Pulido172.jpeg'),
  array('url' => '/IMG/proyectos/26/Pulido173.jpeg','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'Pulido173.jpeg'),
  array('url' => '/IMG/proyectos/26/Pulido174.jpeg','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'Pulido174.jpeg'),
  array('url' => '/IMG/proyectos/26/Pulido175.jpeg','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'Pulido175.jpeg'),
  array('url' => '/IMG/proyectos/26/video.mp4','proyecto' => '26','descripcion' => 'Marqesina','nombre' => 'video.mp4'),
  array('url' => '/IMG/proyectos/27/Pulido189.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido189.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido190.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido190.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido191.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido191.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido192.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido192.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido193.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido193.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido194.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido194.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido195.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido195.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido196.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido196.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido197.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido197.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido198.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido198.jpeg'),
  array('url' => '/IMG/proyectos/27/Pulido199.jpeg','proyecto' => '27','descripcion' => '','nombre' => 'Pulido199.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido176.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido176.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido177.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido177.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido178.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido178.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido179.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido179.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido180.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido180.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido181.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido181.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido182.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido182.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido183.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido183.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido184.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido184.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido185.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido185.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido186.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido186.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido187.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido187.jpeg'),
  array('url' => '/IMG/proyectos/28/Pulido188.jpeg','proyecto' => '28','descripcion' => '','nombre' => 'Pulido188.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1173.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1173.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1172.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1172.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1171.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1171.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1170.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1170.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1169.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1169.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1168.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1168.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1059.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1059.jpeg'),
  array('url' => '/IMG/proyectos/30/Video1186.mp4','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Video1186.mp4'),
  array('url' => '/IMG/proyectos/38/Estructuras1058.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1058.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1057.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1057.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1056.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1056.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1055.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1055.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1054.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1054.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1053.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1053.jpeg'),
  array('url' => '/IMG/proyectos/38/Estructuras1052.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1052.jpeg'),
  array('url' => '/IMG/proyectos/31/Video1188.mp4','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Video1188.mp4'),
  array('url' => '/IMG/proyectos/38/Estructuras1051.jpeg','proyecto' => '38','descripcion' => 'Marquesinas','nombre' => 'Estructuras1051.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1050.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1050.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1049.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1049.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1048.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1048.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1047.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1047.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1046.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1046.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1045.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1045.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1044.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1044.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1043.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1043.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1042.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo 
con perfilería en  aluminio','nombre' => 'Estructuras1042.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1167.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1167.jpeg'),
  array('url' => '/IMG/proyectos/37/Estructuras1041.jpeg','proyecto' => '37','descripcion' => 'Fachada en vidrio reflectivo con perfilería en  aluminio','nombre' => 'Estructuras1041.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1165.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1165.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1166.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1166.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1163.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1163.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1164.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1164.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1161.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1161.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1162.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1162.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1159.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1159.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1160.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1160.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1157.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1157.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1158.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1158.jpeg'),
  array('url' => '/IMG/proyectos/36/Estructuras1156.jpeg','proyecto' => '36','descripcion' => 'Espejos','nombre' => 'Estructuras1156.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1136.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1136.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1137.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1137.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1135.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1135.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1134.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1134.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1133.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1133.jpeg'),
  array('url' => '/IMG/proyectos/35/Estructuras1132.jpeg','proyecto' => '35','descripcion' => 'Divisiones de baño en vidrio y acero','nombre' => 'Estructuras1132.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1155.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1155.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1154.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1154.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1153.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1153.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1152.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1152.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1151.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1151.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1150.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1150.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1149.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1149.jpeg'),
  array('url' => '/IMG/proyectos/34/Estructuras1148.jpeg','proyecto' => '34','descripcion' => 'Divisiones de baño en aluminio y acrílico','nombre' => 'Estructuras1148.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1147.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1147.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1146.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1146.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1145.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1145.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1144.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1144.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1143.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1143.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1142.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1142.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1141.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1141.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1140.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1140.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1139.jpeg','proyecto' => '33','descripcion' => 'Corta 
vientos','nombre' => 'Estructuras1139.jpeg'),
  array('url' => '/IMG/proyectos/33/Estructuras1138.jpeg','proyecto' => '33','descripcion' => 'Corta vientos','nombre' => 'Estructuras1138.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1077.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1077.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1076.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1076.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1075.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1075.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1074.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1074.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1073.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1073.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1072.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1072.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1071.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado 
temprano con serigrafía','nombre' => 'Estructuras1071.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1070.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1070.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1069.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1069.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1068.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1068.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1067.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1067.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1066.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1066.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1065.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1065.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1064.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1064.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1063.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1063.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1062.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1062.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1061.jpeg','proyecto' => '32','descripcion' => 'Baterías para 
baños en acero inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1061.jpeg'),
  array('url' => '/IMG/proyectos/32/Estructuras1060.jpeg','proyecto' => '32','descripcion' => 'Baterías para baños en acero 
inoxidable o vidrio laminado temprano con serigrafía','nombre' => 'Estructuras1060.jpeg'),
  array('url' => '/IMG/proyectos/31/Video1188.mp4','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Video1188.mp4'),
  array('url' => '/IMG/proyectos/31/Estructuras1131.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1131.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1130.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1130.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1129.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1129.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1128.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1128.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1127.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1127.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1126.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1126.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1125.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1125.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1124.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1124.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1123.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1123.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1122.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1122.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1121.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1121.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1120.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1120.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1119.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1119.jpeg'),
  array('url' => '/IMG/proyectos/31/Estructuras1118.jpeg','proyecto' => '31','descripcion' => 'Barandas','nombre' => 'Estructuras1118.jpeg'),
  array('url' => '/IMG/proyectos/30/Video1186.mp4','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Video1186.mp4'),
  array('url' => '/IMG/proyectos/30/Estructuras1040.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1040.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1039.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1039.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1038.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1038.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1037.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1037.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1036.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1036.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1035.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1035.jpeg'),
  array('url' => '/IMG/proyectos/30/Estructuras1034.jpeg','proyecto' => '30','descripcion' => 'Baranda en acero y vidrio templado  con puerta balcon','nombre' => 'Estructuras1034.jpeg'),
  array('url' => '/IMG/proyectos/41/Video1187.mp4','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Video1187.mp4'),
  array('url' => '/IMG/proyectos/39/Estructuras1174.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1174.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1175.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1175.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1176.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1176.jpeg'),
  array('url' => '/IMG/proyectos/39/Estructuras1177.jpeg','proyecto' => '39','descripcion' => 'Puertas en pintura','nombre' => 'Estructuras1177.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1178.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1178.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1179.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1179.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1180.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1180.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1181.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1181.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1182.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1182.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1183.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1183.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1184.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1184.jpeg'),
  array('url' => '/IMG/proyectos/40/Estructuras1185.jpeg','proyecto' => '40','descripcion' => 'Puertas laminadas','nombre' => 'Estructuras1185.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1078.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1078.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1079.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1079.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1080.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1080.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1081.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1081.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1082.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1082.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1083.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1083.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1084.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1084.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1085.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1085.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1086.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1086.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1087.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1087.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1088.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1088.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1089.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1089.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1090.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1090.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1091.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1091.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1092.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1092.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1093.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1093.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1094.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1094.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1095.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1095.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1096.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1096.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1097.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1097.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1098.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1098.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1099.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1099.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1100.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1100.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1101.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1101.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1102.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1102.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1103.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1103.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1104.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1104.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1105.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1105.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1106.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1106.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1107.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1107.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1108.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1108.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1109.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1109.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1110.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1110.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1111.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1111.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1112.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1112.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1113.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1113.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1114.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1114.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1115.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1115.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1116.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1116.jpeg'),
  array('url' => '/IMG/proyectos/41/Estructuras1117.jpeg','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Estructuras1117.jpeg'),
  array('url' => '/IMG/proyectos/41/Video1187.mp4','proyecto' => '41','descripcion' => 'Ventaneria en aluminio y vidrio','nombre' => 'Video1187.mp4'),
  array('url' => '/IMG/proyectos/42/07.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '07.jpg'),
  array('url' => '/IMG/proyectos/42/06.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '06.jpg'),
  array('url' => '/IMG/proyectos/42/05.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '05.jpg'),
  array('url' => '/IMG/proyectos/42/04.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '04.jpg'),
  array('url' => '/IMG/proyectos/42/03.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '03.jpg'),
  array('url' => '/IMG/proyectos/42/02.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '02.jpg'),
  array('url' => '/IMG/proyectos/42/01.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '01.jpg'),
  array('url' => '/IMG/proyectos/42/08.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '08.jpg'),
  array('url' => '/IMG/proyectos/42/10.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '10.jpg'),
  array('url' => '/IMG/proyectos/42/09.jpg','proyecto' => '42','descripcion' => 'Fachadas decorativas, materiales y diseños inovadores','nombre' => '09.jpg')

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
