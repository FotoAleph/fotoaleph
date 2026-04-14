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
            $table->unsignedInteger('nivel')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('tematicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('nivel')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('colores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_hexadecimal')->nullable();
            $table->string('descripcion')->nullable();
            $table->unsignedInteger('nivel')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha_evento')->nullable();
            $table->date('entregado')->nullable();
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
            $table->string('aspect_ratio')->nullable();
            $table->string('alt')->nullable();
           
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('muestrarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('ocasion_id')->nullable()->constrained('ocasiones')->nullOnDelete();
            $table->foreignId('tematica_id')->nullable()->constrained('tematicas')->nullOnDelete();
            $table->foreignId('color_id')->nullable()->constrained('colores')->nullOnDelete();
            $table->text('descripcion')->nullable();
            $table->foreignId('multimedia_id')->nullable()->constrained('multimedia')->nullOnDelete();
            $table->unsignedInteger('nivel')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('evento_multimedia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
            $table->unsignedTinyInteger('cantidad')->default(0);
            $table->timestamps();
        });

        Schema::connection('tenant_casa_angel')->create('muestrarios_etiquetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('muestrario_id')->constrained('muestrarios')->onDelete('cascade');
            $table->string('keyword');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::connection('tenant_casa_angel')->dropIfExists('evento_multimedia');
        Schema::connection('tenant_casa_angel')->dropIfExists('muestrarios');
        Schema::connection('tenant_casa_angel')->dropIfExists('multimedia');
        Schema::connection('tenant_casa_angel')->dropIfExists('eventos');
        Schema::connection('tenant_casa_angel')->dropIfExists('tematicas');
        Schema::connection('tenant_casa_angel')->dropIfExists('colores');
        Schema::connection('tenant_casa_angel')->dropIfExists('ocasiones');
    }
};
