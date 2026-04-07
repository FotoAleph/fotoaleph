<?php

use App\Models\Evento;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('tenant_casa_angel');

        if (! $schema->hasTable('colores')) {
            $schema->create('colores', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('codigo_hexadecimal')->nullable();
                $table->string('descripcion')->nullable();
                $table->unsignedInteger('nivel')->default(0);
                $table->timestamps();
            });
        }

        $this->addColumnIfMissing('ocasiones', 'nivel', fn (Blueprint $table) => $table->unsignedInteger('nivel')->default(0));
        $this->addColumnIfMissing('tematicas', 'nivel', fn (Blueprint $table) => $table->unsignedInteger('nivel')->default(0));
        $this->addColumnIfMissing('colores', 'nivel', fn (Blueprint $table) => $table->unsignedInteger('nivel')->default(0));
        $this->addColumnIfMissing('eventos', 'color_id', fn (Blueprint $table) => $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete());
        $this->addColumnIfMissing('eventos', 'codigo', fn (Blueprint $table) => $table->string('codigo')->nullable());

        if (! $schema->hasColumn('eventos', 'publicar_en_vitrina')) {
            $schema->table('eventos', function (Blueprint $table) {
                $table->boolean('publicar_en_vitrina')->default(false);
            });

            DB::connection('tenant_casa_angel')->table('eventos')->update(['publicar_en_vitrina' => 1]);
        }

        if (! $schema->hasTable('evento_multimedia')) {
            $schema->create('evento_multimedia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if ($schema->hasTable('multimediable') && ! DB::connection('tenant_casa_angel')->table('evento_multimedia')->exists()) {
            $rows = DB::connection('tenant_casa_angel')
                ->table('multimediable')
                ->where('multimediable_type', Evento::class)
                ->select(['multimediable_id', 'multimedia_id', 'created_at', 'updated_at'])
                ->get()
                ->map(fn ($row) => [
                    'evento_id' => $row->multimediable_id,
                    'multimedia_id' => $row->multimedia_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])
                ->all();

            if ($rows !== []) {
                DB::connection('tenant_casa_angel')->table('evento_multimedia')->insert($rows);
            }
        }

        if ($schema->hasTable('multimediable')) {
            $schema->drop('multimediable');
        }

        $this->backfillEventMetadata();
    }

    public function down(): void
    {
        $schema = Schema::connection('tenant_casa_angel');

        if (! $schema->hasTable('multimediable')) {
            $schema->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->string('multimediable_type');
                $table->unsignedBigInteger('multimediable_id');
                $table->timestamps();
            });
        }

        if ($schema->hasTable('evento_multimedia') && ! DB::connection('tenant_casa_angel')->table('multimediable')->exists()) {
            $rows = DB::connection('tenant_casa_angel')
                ->table('evento_multimedia')
                ->select(['evento_id', 'multimedia_id', 'created_at', 'updated_at'])
                ->get()
                ->map(fn ($row) => [
                    'multimediable_type' => Evento::class,
                    'multimediable_id' => $row->evento_id,
                    'multimedia_id' => $row->multimedia_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])
                ->all();

            if ($rows !== []) {
                DB::connection('tenant_casa_angel')->table('multimediable')->insert($rows);
            }
        }

        $schema->dropIfExists('evento_multimedia');
    }

    private function addColumnIfMissing(string $table, string $column, Closure $definition): void
    {
        $schema = Schema::connection('tenant_casa_angel');

        if ($schema->hasColumn($table, $column)) {
            return;
        }

        $schema->table($table, function (Blueprint $blueprint) use ($definition) {
            $definition($blueprint);
        });
    }

    private function backfillEventMetadata(): void
    {
        $events = DB::connection('tenant_casa_angel')->table('eventos')->get();

        foreach ($events as $event) {
            $description = (string) ($event->descripcion ?? '');

            $occasionName = $this->extractLabeledValue($description, 'Ocasión') ?? 'General';
            $themeName = $this->extractLabeledValue($description, 'Temática') ?? 'General';
            $colorName = $this->inferColorName(implode(' ', array_filter([$event->nombre, $description])));

            $occasionId = $this->firstOrCreateCatalog('ocasiones', $occasionName);
            $themeId = $this->firstOrCreateCatalog('tematicas', $themeName);
            $colorId = $colorName ? $this->firstOrCreateCatalog('colores', $colorName, true) : null;

            DB::connection('tenant_casa_angel')
                ->table('eventos')
                ->where('id', $event->id)
                ->update([
                    'ocasion_id' => $event->ocasion_id ?: $occasionId,
                    'tematica_id' => $event->tematica_id ?: $themeId,
                    'color_id' => $event->color_id ?: $colorId,
                ]);
        }
    }

    private function extractLabeledValue(string $description, string $label): ?string
    {
        $pattern = '/'.preg_quote($label, '/').':\s*([^\.]+)/iu';

        if (! preg_match($pattern, $description, $matches)) {
            return null;
        }

        return Str::title(trim($matches[1]));
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

    private function firstOrCreateCatalog(string $table, string $name, bool $withHex = false): int
    {
        $existing = DB::connection('tenant_casa_angel')
            ->table($table)
            ->where('nombre', $name)
            ->first();

        if ($existing !== null) {
            return (int) $existing->id;
        }

        return (int) DB::connection('tenant_casa_angel')->table($table)->insertGetId(array_filter([
            'nombre' => $name,
            'descripcion' => $name,
            'codigo_hexadecimal' => $withHex ? null : null,
            'nivel' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], static fn ($value, $key) => $key !== 'codigo_hexadecimal' || $withHex, ARRAY_FILTER_USE_BOTH));
    }
};