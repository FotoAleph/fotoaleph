<?php

namespace App\Support\Vitrinas;

use App\Models\Color;
use App\Models\Evento;
use App\Models\Nivel;
use App\Models\Ocasion;
use App\Models\Tematica;
use App\Models\Vitrina;
use Illuminate\Support\Str;

class VitrinaInteractionTracker
{
    public function record(Vitrina $vitrina): array
    {
        $vitrina->loadMissing(['tenant', 'categoria', 'grupo', 'nivel', 'items']);

        $itemLevel = $this->incrementVitrinaLevel($vitrina);
        $metadataLevels = $this->incrementTenantMetadata($vitrina);

        return [
            'id' => $vitrina->id,
            'level' => $itemLevel,
            'metadata' => $metadataLevels,
        ];
    }

    private function incrementVitrinaLevel(Vitrina $vitrina): int
    {
        $nivel = Nivel::query()->firstOrCreate(
            [
                'nivelable_type' => Vitrina::class,
                'nivelable_id' => $vitrina->id,
            ],
            ['nivel' => 0],
        );

        $nivel->increment('nivel');

        return (int) $nivel->fresh()->nivel;
    }

    private function incrementTenantMetadata(Vitrina $vitrina): array
    {
        if ($vitrina->tenant?->databaseConnectionName() !== 'tenant_casa_angel') {
            return [];
        }

        $occasionLevel = $this->incrementNamedLevel(Ocasion::class, $vitrina->categoria?->nombre);
        $themeLevel = $this->incrementNamedLevel(Tematica::class, $vitrina->grupo?->nombre);
        $colorLevel = $this->incrementNamedLevel(Color::class, $this->resolveCasaAngelColor($vitrina));

        return array_filter([
            'ocasion' => $occasionLevel,
            'tematica' => $themeLevel,
            'color' => $colorLevel,
        ], static fn ($value) => $value !== null);
    }

    private function incrementNamedLevel(string $modelClass, ?string $name): ?int
    {
        $normalized = trim((string) $name);

        if ($normalized === '') {
            return null;
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $modelClass::query()->firstOrCreate(
            ['nombre' => Str::title($normalized)],
            ['descripcion' => Str::title($normalized), 'nivel' => 0],
        );

        $model->increment('nivel');

        return (int) $model->fresh()->nivel;
    }

    private function resolveCasaAngelColor(Vitrina $vitrina): ?string
    {
        $sourceItem = $vitrina->items
            ->first(fn ($item) => $item->source_type === Evento::class && $item->source_connection === 'tenant_casa_angel');

        if ($sourceItem !== null) {
            $evento = Evento::query()->with('color')->find($sourceItem->source_id);

            if ($evento?->color?->nombre) {
                return $evento->color->nombre;
            }

            $fromEvent = $this->inferColorName(implode(' ', array_filter([
                $evento?->nombre,
                $evento?->descripcion,
            ])));

            if ($fromEvent !== null) {
                return $fromEvent;
            }
        }

        return $this->inferColorName(implode(' ', array_filter([
            $vitrina->grupo?->nombre,
            $vitrina->nombre,
            $vitrina->descripcion,
        ])));
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
}