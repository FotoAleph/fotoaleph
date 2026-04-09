<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Muestra extends Model
{

            // $table->id();
            // $table->string('nombre');
            // $table->foreignId('ocasion_id')->nullable()->constrained('ocasiones')->nullOnDelete();
            // $table->foreignId('tematica_id')->nullable()->constrained('tematicas')->nullOnDelete();
            // $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete();
            // $table->text('descripcion')->nullable();
            // $table->foreignId('multimedia_id')->nullable()->constrained('multimedia')->nullOnDelete();
            // $table->unsignedInteger('nivel')->default(0);
            // $table->timestamps();
    protected $connection = 'tenant_casa_angel';
    protected $fillable = [
        'nombre',
        'ocasion_id',
        'tematica_id',
        'color_id',
        'descripcion',
        'multimedia_id',
        'nivel',
    ];
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];
    protected $table = 'muestrarios';

    public function ocasion()
    {
        return $this->belongsTo(Ocasion::class);
    }

    public function tematica()
    {
        return $this->belongsTo(Tematica::class);
    }

    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    public function multimedia()
    {
        return $this->belongsTo(Multimedia::class);
    }
    
}
