<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Estudiante extends Model
{
    protected $connection = 'tenant_sport_bogota';

    protected $fillable = [
        'nombre',
        'categoria',
    ];

    protected $table = 'estudiantes';

    public function multimedias(): BelongsToMany
    {
        return $this->belongsToMany(Multimedia::class, 'estudiante_multimedia')
            ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}
