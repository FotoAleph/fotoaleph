<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialNetworkType extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'base_url',
    ];

    public function socialNetworks(): HasMany
    {
        return $this->hasMany(SocialNetwork::class);
    }
}
