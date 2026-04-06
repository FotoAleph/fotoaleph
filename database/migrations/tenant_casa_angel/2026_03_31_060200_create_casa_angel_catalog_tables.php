<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant_casa_angel')->create('ocasiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('tematicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
        Schema::connection('tenant_casa_angel')->create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_hexadecimal')->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_evento')->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('codigo')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('multimedia', function (Blueprint $table) {
                $table->id();
                $table->string('url');
                $table->string('preview_url')->nullable();
                $table->string('type')->default('image');
                $table->string('mime_type')->nullable();
                $table->timestamps();
            });

        Schema::connection('tenant_casa_angel')->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->morphs('multimediable');        
                $table->timestamps();
            }); 
            
        Schema::connection('tenant_casa_angel')->create('vitrinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('ocasion_id')->nullable()->constrained('ocasiones')->nullOnDelete();
            $table->foreignId('tematica_id')->nullable()->constrained('tematicas')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    
    }

    public function down(): void
    {
        Schema::connection('tenant_casa_angel')->dropIfExists('vitrinas');
        Schema::connection('tenant_casa_angel')->dropIfExists('multimediable');
        Schema::connection('tenant_casa_angel')->dropIfExists('multimedia');
        Schema::connection('tenant_casa_angel')->dropIfExists('eventos');
        Schema::connection('tenant_casa_angel')->dropIfExists('tematicas');
        Schema::connection('tenant_casa_angel')->dropIfExists('colores');
        Schema::connection('tenant_casa_angel')->dropIfExists('ocasiones');
    }
};
