<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        "razon_social"
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function sitios()
    {
        return $this->hasMany(Sitio::class);
    }
    public function direcciones()
    {
        return $this->morphMany(Direccion::class, 'direccionable');
    }
    public function telefonos()
    {
        return $this->morphMany(Telefono::class, 'telefonoable');
    }
    public function redesSociales()
    {
        return $this->morphMany(SocialNetwork::class, 'socialable');    
    }
    public function aleatoriasRedesSociales()
    {
        return $this->morphMany(SocialNetwork::class, 'socialable')->with('socialNetworkType')->inRandomOrder()->limit(2)->get()->map(function ($network) {
            return [
                'name' => $network->socialNetworkType->name,
                'url' => $network->url,
                'icon' => '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="' . $network->socialNetworkType->icon . '"/></svg>',
            ];
        });
    }
}
