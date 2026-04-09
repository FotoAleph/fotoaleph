<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaMultimedia extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $table = 'multimedia';

    protected $fillable = [
        'url',
        'preview_url',
        'type',
        'mime_type',
        'aspect_ratio',
        'alt',
        'orientacion',
        'nivel',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',     
        'nivel' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            Muestra::bustApiCache();
        });

        static::deleted(function (): void {
            Muestra::bustApiCache();
        });
    }
}
