<?php

namespace App\Pipelines\Estudiantes;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class FiltrarPorNombre
{
    public function __construct(private readonly ?string $nombre) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $nombre = trim((string) $this->nombre);

        if ($nombre !== '') {
            $query->where('nombre', 'like', "%{$nombre}%");
        }

        return $next($query);
    }
}
