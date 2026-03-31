<?php

namespace App\Pipelines\Vitrinas;

use App\Pipelines\Vitrinas\Concerns\InterpretsFilterValue;
use Closure;
use Illuminate\Support\Collection;

class FiltrarPorGrupo
{
    use InterpretsFilterValue;

    public function __construct(private readonly mixed $grupo) {}

    public function handle(Collection $vitrinas, Closure $next): Collection
    {
        $filtro = $this->normalizeFilterValue($this->grupo);

        if ($filtro['id'] === null && $filtro['nombre'] === null) {
            return $next($vitrinas);
        }

        $filtradas = $vitrinas->filter(function ($vitrina) use ($filtro) {
            $grupo = $vitrina->grupo;

            if ($grupo === null) {
                return false;
            }

            if ($filtro['id'] !== null) {
                return (int) $grupo->getKey() === $filtro['id'];
            }

            return strcasecmp((string) $grupo->nombre, (string) $filtro['nombre']) === 0;
        });

        return $next($filtradas);
    }
}
