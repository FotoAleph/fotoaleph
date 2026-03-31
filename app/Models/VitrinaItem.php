<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitrinaItem extends Model
{
    protected $fillable = [
        'vitrina_id',
        'multimedia_id',
        'source_type',
        'source_id',
        'source_connection',
        'orden',
        'es_portada',
    ];

    protected $casts = [
        'es_portada' => 'boolean',
    ];

    public function vitrina(): BelongsTo
    {
        return $this->belongsTo(Vitrina::class);
    }

    public function multimedia(): BelongsTo
    {
        return $this->belongsTo(Multimedia::class);
    }
}
