<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Tenant extends Model
{
    protected $fillable = [
        'razon_social',
        'database_connection',
    ];

    public function databaseConnectionName(): string
    {
        return $this->database_connection ?: 'tenant_central';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function sitios(): HasMany
    {
        return $this->hasMany(Sitio::class);
    }

    public function vitrinas(): HasMany
    {
        return $this->hasMany(Vitrina::class);
    }

    public function direcciones(): MorphMany
    {
        return $this->morphMany(Direccion::class, 'direccionable');
    }

    public function telefonos(): MorphMany
    {
        return $this->morphMany(Telefono::class, 'telefonoable');
    }

    public function redesSociales(): MorphMany
    {
        return $this->morphMany(SocialNetwork::class, 'socialable');
    }

    public function aleatoriasRedesSociales()
    {
        return $this->morphMany(SocialNetwork::class, 'socialable')->with('socialNetworkType')->inRandomOrder()->limit(2)->get()->map(function ($network) {
            return [
                'name' => $network->socialNetworkType->name ?? '',
                'url' => $network->url,
                'icon' => '🏴‍☠️' // Placeholder for the icon, replace with actual icon logic if needed
            ];
        });
    }
}
