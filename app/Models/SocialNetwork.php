<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialNetwork extends Model
{
    protected $table = 'redes_sociales';

    protected $fillable = [
        'socialable_id',
        'socialable_type',
        'social_network_type_id',
        'url',
    ];

    public function socialable(): MorphTo
    {
        return $this->morphTo();
    }

    public function socialNetworkType(): BelongsTo
    {
        return $this->belongsTo(SocialNetworkType::class);
    }
}
