<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        "razon_social",
    ];

    public function users()
    {
        return $this->hasMany(User::class);
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
}
