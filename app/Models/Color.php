<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $table = 'colores';

    protected $fillable = [
        'nombre',
        'codigo_hexadecimal',
        'descripcion',
        'nivel',
    ];

    protected $casts = [
        'nivel' => 'integer',
    ];

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }
}