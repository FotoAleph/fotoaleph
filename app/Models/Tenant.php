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
    ];

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
                'name' => $network->socialNetworkType->name,
                'url' => $network->url,
                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="'.$network->socialNetworkType->icon.'"/></svg>',
            ];
        });
    }
}
