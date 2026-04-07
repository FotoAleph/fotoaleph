<?php

namespace Tests\Unit;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\Vitrina;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

class VitrinaFilterPipelineTest extends TestCase
{
    public function test_it_filters_by_categoria_and_grupo_and_orders_by_fecha_and_nivel(): void
    {
        Carbon::setTestNow('2026-03-30 10:00:00');

        $primera = new Vitrina(['nombre' => 'Primera']);
        $primera->id = 1;
        $primera->created_at = Carbon::parse('2026-03-20 08:00:00');
        $primera->setRelation('categoria', new Categoria(['nombre' => 'Arte']));
        $primera->setRelation('grupo', new Grupo(['nombre' => 'A']));
        $primera->setRelation('nivel', new Nivel(['nivel' => 2]));

        $segunda = new Vitrina(['nombre' => 'Segunda']);
        $segunda->id = 2;
        $segunda->created_at = Carbon::parse('2026-03-25 08:00:00');
        $segunda->setRelation('categoria', new Categoria(['nombre' => 'Arte']));
        $segunda->setRelation('grupo', new Grupo(['nombre' => 'A']));
        $segunda->setRelation('nivel', new Nivel(['nivel' => 1]));

        $tercera = new Vitrina(['nombre' => 'Tercera']);
        $tercera->id = 3;
        $tercera->created_at = Carbon::parse('2026-03-29 08:00:00');
        $tercera->setRelation('categoria', new Categoria(['nombre' => 'Foto']));
        $tercera->setRelation('grupo', new Grupo(['nombre' => 'B']));
        $tercera->setRelation('nivel', new Nivel(['nivel' => 3]));

        $resultado = Vitrina::filtrar(
            [
                'categoria' => 'Arte',
                'grupo' => 'A',
                'direccion_fecha' => 'desc',
                'direccion_nivel' => 'asc',
            ],
            new Collection([$primera, $segunda, $tercera]),
        );

        $this->assertCount(2, $resultado);
        $this->assertSame(['Segunda', 'Primera'], $resultado->pluck('nombre')->all());
    }

    public function test_it_prioritizes_nivel_before_fecha_when_sorting(): void
    {
        $nivelBajoMasAntiguo = new Vitrina(['nombre' => 'Nivel Bajo']);
        $nivelBajoMasAntiguo->id = 1;
        $nivelBajoMasAntiguo->created_at = Carbon::parse('2026-03-20 08:00:00');
        $nivelBajoMasAntiguo->setRelation('nivel', new Nivel(['nivel' => 1]));

        $nivelAltoMasReciente = new Vitrina(['nombre' => 'Nivel Alto']);
        $nivelAltoMasReciente->id = 2;
        $nivelAltoMasReciente->created_at = Carbon::parse('2026-03-25 08:00:00');
        $nivelAltoMasReciente->setRelation('nivel', new Nivel(['nivel' => 5]));

        $resultado = Vitrina::filtrar(
            [
                'direccion_fecha' => 'desc',
                'direccion_nivel' => 'asc',
            ],
            new Collection([$nivelAltoMasReciente, $nivelBajoMasAntiguo]),
        );

        $this->assertSame(['Nivel Bajo', 'Nivel Alto'], $resultado->pluck('nombre')->all());
    }

    public function test_it_orders_consistently_by_nivel_when_created_at_is_the_same(): void
    {
        $fecha = Carbon::parse('2026-03-31 22:53:19');

        $vitrinas = new Collection([
            $this->makeVitrina(1, 'Nivel 2A', 2, $fecha),
            $this->makeVitrina(2, 'Nivel 4A', 4, $fecha),
            $this->makeVitrina(3, 'Nivel 2B', 2, $fecha),
            $this->makeVitrina(4, 'Nivel 5A', 5, $fecha),
            $this->makeVitrina(5, 'Nivel 1A', 1, $fecha),
            $this->makeVitrina(6, 'Nivel 4B', 4, $fecha),
        ]);

        $resultado = Vitrina::filtrar(
            [
                'direccion_fecha' => 'desc',
                'direccion_nivel' => 'desc',
            ],
            $vitrinas,
        );

        $this->assertSame([5, 4, 4, 2, 2, 1], $resultado->pluck('nivel.nivel')->map(fn ($nivel) => (int) $nivel)->all());
    }

    private function makeVitrina(int $id, string $nombre, int $nivel, Carbon $createdAt): Vitrina
    {
        $vitrina = new Vitrina(['nombre' => $nombre]);
        $vitrina->id = $id;
        $vitrina->created_at = $createdAt;
        $vitrina->setRelation('nivel', new Nivel(['nivel' => $nivel]));

        return $vitrina;
    }
}
