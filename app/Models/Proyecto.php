<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Proyecto extends Model
{
    protected $connection = 'tenant_jym';

    protected $fillable = [
        'categoria_id',
        'grupo_id',
        'nombre',
        'descripcion',
        'materiales',

    ];

    protected $casts = [
        'materiales' => 'array',
        'publicar_en_vitrina' => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(JymCategoria::class, 'categoria_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(JymGrupo::class, 'grupo_id');
    }

    public function niveles(): MorphMany
    {
        return $this->morphMany(Nivel::class, 'nivelable');
    }

    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'multimedia_proyecto')
           
            ->orderBy('multimedia.nivel', 'desc')
            

            ->withTimestamps();
    }

    public function imaganes(): BelongsToMany
    {
            return $this->belongsToMany(Multimedia::class, 'multimedia_proyecto')
            ->where('multimedia.type', 'image')
            ->orderBy('multimedia.nivel', 'desc')
            
           ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}
