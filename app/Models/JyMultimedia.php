<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JyMultimedia extends Model
{
    protected $connection = 'tenant_jym';

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
}
