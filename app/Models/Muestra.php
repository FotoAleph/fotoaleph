<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Muestra extends Model
{

    private const API_CACHE_VERSION_KEY = 'tenant:casa_angel:api:muestrarios:index:version';

            // $table->id();
            // $table->string('nombre');
            // $table->foreignId('ocasion_id')->nullable()->constrained('ocasiones')->nullOnDelete();
            // $table->foreignId('tematica_id')->nullable()->constrained('tematicas')->nullOnDelete();
            // $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete();
            // $table->text('descripcion')->nullable();
            // $table->foreignId('multimedia_id')->nullable()->constrained('multimedia')->nullOnDelete();
            // $table->unsignedInteger('nivel')->default(0);
            // $table->timestamps();
    protected $connection = 'tenant_casa_angel';
    protected $fillable = [
        'nombre',
        'ocasion_id',
        'tematica_id',
        'color_id',
        'descripcion',
        'multimedia_id',
        'nivel',
    ];
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    protected $table = 'muestrarios';

    protected static function booted(): void
    {
        static::saved(function (): void {
            self::bustApiCache();
        });

        static::deleted(function (): void {
            self::bustApiCache();
        });
    }

    public static function apiCacheKey(array $query = []): string
    {
        $queryHash = $query === []
            ? 'all'
            : sha1(http_build_query($query));

        return sprintf(
            'tenant:casa_angel:api:muestrarios:index:v%d:%s',
            self::apiCacheVersion(),
            $queryHash,
        );
    }

    public static function bustApiCache(): void
    {
        Cache::forever(self::API_CACHE_VERSION_KEY, self::apiCacheVersion() + 1);
    }

    private static function apiCacheVersion(): int
    {
        return max(1, (int) Cache::get(self::API_CACHE_VERSION_KEY, 1));
    }

    public function ocasion()
    {
        return $this->belongsTo(Ocasion::class);
    }

    public function tematica()
    {
        return $this->belongsTo(Tematica::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function multimedia(): BelongsTo
    {
        return $this->belongsTo(CaMultimedia::class, 'multimedia_id');
    }
    
}
