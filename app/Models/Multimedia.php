<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Multimedia extends Model
{
    protected $table = 'multimedia';

    protected $fillable = [
        'url',
        'type',
        'mime_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all of the models that own multimedia files.
     */
    public function multimediable(): MorphToMany
    {
        return $this->morphedByMany(
            Model::class,
            'multimediable',
            'multimediable',
            'multimedia_id'
        );
    }
}
