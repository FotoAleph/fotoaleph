<?php

namespace App\Pipelines\Vitrinas;

use App\Pipelines\Vitrinas\Concerns\InterpretsFilterValue;
use Closure;
use Illuminate\Support\Collection;

class FiltrarPorCategoria
{
    use InterpretsFilterValue;

    public function __construct(private readonly mixed $categoria) {}

    public function handle(Collection $vitrinas, Closure $next): Collection
    {
        $filtro = $this->normalizeFilterValue($this->categoria);

        if ($filtro['id'] === null && $filtro['nombre'] === null) {
            return $next($vitrinas);
        }

        $filtradas = $vitrinas->filter(function ($vitrina) use ($filtro) {
            $categoria = $vitrina->categoria;

            if ($categoria === null) {
                return false;
            }

            if ($filtro['id'] !== null) {
                return (int) $categoria->getKey() === $filtro['id'];
            }

            return strcasecmp((string) $categoria->nombre, (string) $filtro['nombre']) === 0;
        });

        return $next($filtradas);
    }
}
