<?php

namespace App\Support\Tenants;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Multimedia;
use App\Models\Tenant;
use App\Models\Vitrina;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TenantCatalogVitrinaSynchronizer
{
    public function sync(
        Tenant $tenant,
        Model $source,
        string $nombre,
        ?string $descripcion,
        ?string $categoria,
        ?string $grupo,
        Collection $mediaItems,
        bool $publicar,
        int $nivel = 0,
    ): void {
        $existing = $this->existingVitrina($tenant, $source);

        if (! $publicar || $mediaItems->isEmpty()) {
            $existing?->delete();

            return;
        }

        $vitrina = $existing ?? Vitrina::query()->create([
            'tenant_id' => $tenant->id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ]);

        $vitrina->fill([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
        ])->save();

        $payload = $mediaItems->values()->mapWithKeys(function (Multimedia $media, int $index) use ($source) {
            $centralMedia = Multimedia::query()->firstOrCreate(
                [
                    'url' => $media->url,
                    'preview_url' => $media->preview_url,
                    'type' => $media->type,
                ],
                [
                    'mime_type' => $media->mime_type,
                ],
            );

            return [
                $centralMedia->id => [
                    'source_type' => $source::class,
                    'source_id' => $source->getKey(),
                    'source_connection' => $source->getConnectionName(),
                    'orden' => $index,
                    'es_portada' => $index === 0,
                ],
            ];
        })->all();

        $vitrina->multimedias()->sync($payload);

        $this->syncMorphMetadata($vitrina, $categoria, $grupo, $nivel);
    }

    private function existingVitrina(Tenant $tenant, Model $source): ?Vitrina
    {
        return Vitrina::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('items', function ($query) use ($source) {
                $query
                    ->where('source_type', $source::class)
                    ->where('source_id', $source->getKey())
                    ->where('source_connection', $source->getConnectionName());
            })
            ->first();
    }

    private function syncMorphMetadata(Vitrina $vitrina, ?string $categoria, ?string $grupo, int $nivel): void
    {
        if ($categoria !== null && $categoria !== '') {
            $vitrina->categoria()->updateOrCreate([], [
                'nombre' => $categoria,
                'descripcion' => $categoria,
            ]);
        } else {
            Categoria::query()
                ->where('categoriaable_type', Vitrina::class)
                ->where('categoriaable_id', $vitrina->id)
                ->delete();
        }

        if ($grupo !== null && $grupo !== '') {
            $vitrina->grupo()->updateOrCreate([], [
                'nombre' => $grupo,
                'descripcion' => $grupo,
            ]);
        } else {
            Grupo::query()
                ->where('grupoable_type', Vitrina::class)
                ->where('grupoable_id', $vitrina->id)
                ->delete();
        }

        $vitrina->nivel()->updateOrCreate([], [
            'nivel' => $nivel,
        ]);
    }
}