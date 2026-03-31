<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphedByMany;

class Multimedia extends Model
{
    protected $table = 'multimedia';

    protected $fillable = [
        'url',
        'preview_url',
        'type',
        'mime_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vitrinas(): BelongsToMany
    {
        return $this->belongsToMany(Vitrina::class, 'vitrina_items')
            ->withPivot(['source_type', 'source_id', 'source_connection', 'orden', 'es_portada'])
            ->withTimestamps();
    }

    public function proyectos(): MorphedByMany
    {
        return $this->morphedByMany(
            Proyecto::class,
            'multimediable',
            'multimediable',
            'multimedia_id'
        );
    }

    public function eventos(): MorphedByMany
    {
        return $this->morphedByMany(
            Evento::class,
            'multimediable',
            'multimediable',
            'multimedia_id'
        );
    }
}
