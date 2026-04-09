<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Multimedia extends Model
{
    protected $table = 'multimedia';

    protected $fillable = [
        'url',
        'preview_url',
        'type',
        'mime_type',
        'aspect_ratio',
        'alt',
        'orientacion',
        'cantidad',
        'nivel',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cantidad' => 'integer',
        'nivel' => 'integer',
    ];

    public function vitrinas(): BelongsToMany
    {
        return $this->belongsToMany(Vitrina::class, 'vitrina_items')
            ->withPivot(['source_type', 'source_id', 'source_connection', 'orden', 'es_portada'])
            ->withTimestamps();
    }

    public function proyectos(): BelongsToMany
    {
        return $this->belongsToMany(Proyecto::class, 'multimedia_proyecto')
            ->withTimestamps();
    }

    public function eventos(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'evento_multimedia')
            ->withTimestamps();
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'estudiante_multimedia')
            ->withTimestamps();
    }

    public function biotekEstudiantes(): BelongsToMany
    {
        return $this->belongsToMany(BiotekEstudiante::class, 'estudiante_multimedia', 'multimedia_id', 'estudiante_id')
            ->withTimestamps();
    }
}
