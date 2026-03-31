<?php

declare(strict_types=1);

namespace App\Models;

use App\Pipelines\Vitrinas\FiltrarPorCategoria;
use App\Pipelines\Vitrinas\FiltrarPorGrupo;
use App\Pipelines\Vitrinas\OrdenarPorFechaYNivel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class Vitrina extends Model
{
    protected $fillable = [
        'tenant_id',
        'nombre',
        'descripcion',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function categoria(): MorphOne
    {
        return $this->morphOne(Categoria::class, 'categoriaable');
    }

    public function grupo(): MorphOne
    {
        return $this->morphOne(Grupo::class, 'grupoable');
    }

    public function nivel(): MorphOne
    {
        return $this->morphOne(Nivel::class, 'nivelable');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VitrinaItem::class)->orderBy('orden');
    }

    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'vitrina_items')
            ->withPivot(['source_type', 'source_id', 'source_connection', 'orden', 'es_portada'])
            ->withTimestamps()
            ->orderByPivot('es_portada', 'desc')
            ->orderByPivot('orden');
    }

    public function coverMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }

    public function previewImageUrl(): ?string
    {
        $media = $this->coverMedia();

        return $media?->preview_url ?: $media?->url;
    }

    public function detailImageUrl(): ?string
    {
        $media = $this->coverMedia();

        return $media?->url;
    }

    public function coverMediaType(): ?string
    {
        return $this->coverMedia()?->type;
    }

    public static function filtrar(array $filtros = [], ?Collection $vitrinas = null): Collection
    {
        $coleccion = $vitrinas
            ?? static::query()
                ->with(['tenant', 'categoria', 'grupo', 'nivel', 'multimedias'])
                ->get();

        return app(Pipeline::class)
            ->send($coleccion)
            ->through([
                new FiltrarPorCategoria($filtros['categoria'] ?? null),
                new FiltrarPorGrupo($filtros['grupo'] ?? null),
                new OrdenarPorFechaYNivel(
                    $filtros['direccion_fecha'] ?? 'desc',
                    $filtros['direccion_nivel'] ?? 'asc'
                ),
            ])
            ->thenReturn()
            ->values();
    }
}
