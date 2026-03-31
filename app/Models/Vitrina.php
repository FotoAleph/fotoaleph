<?php

declare(strict_types=1);

namespace App\Models;

use App\Pipelines\Vitrinas\FiltrarPorCategoria;
use App\Pipelines\Vitrinas\FiltrarPorGrupo;
use App\Pipelines\Vitrinas\OrdenarPorFechaYNivel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class Vitrina extends Model
{
    protected $fillable = [
        'tenant_id',
        'nombre',
        'descripcion',
        'imagen',
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

    public static function filtrar(array $filtros = [], ?Collection $vitrinas = null): Collection
    {
        $coleccion = $vitrinas
            ?? static::query()
                ->with(['tenant', 'categoria', 'grupo', 'nivel'])
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
