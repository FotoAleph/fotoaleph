<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sitio extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'url',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
