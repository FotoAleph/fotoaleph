<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BiotekEstudiante extends Model
{
    protected $connection = 'tenant_biotek';

    protected $table = 'estudiantes';

    protected $fillable = [
        'nombres',
        'apellidos',
        'identificacion',
    ];

    public function multimedias(): MorphToMany
    {
        return $this->morphToMany(
            Multimedia::class,
            'multimediable',
            'multimediable',
            'multimediable_id',
            'multimedia_id'
        )->withTimestamps();
    }

    public function talleres(): BelongsToMany
    {
        return $this->belongsToMany(Taller::class, 'estudiantes_talleres', 'estudiante_id', 'taller_id')
            ->withPivot(['pago', 'abono', 'debe', 'saldo_total'])
            ->withTimestamps();
    }

    public function primaryMedia(): ?Multimedia
    {
        return $this->multimedias->first();
    }
}