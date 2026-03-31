<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ocasion extends Model
{
    protected $connection = 'tenant_casa_angel';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function eventos(): HasMany
    {
        return $this->hasMany(Evento::class);
    }

    protected $table = 'ocasiones';
}
