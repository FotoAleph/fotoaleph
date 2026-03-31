<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
