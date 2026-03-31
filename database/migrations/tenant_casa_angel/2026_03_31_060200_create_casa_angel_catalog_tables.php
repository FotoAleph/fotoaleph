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

        Schema::connection('tenant_casa_angel')->create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ocasion_id')->nullable()->constrained('ocasiones')->nullOnDelete();
            $table->foreignId('tematica_id')->nullable()->constrained('tematicas')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_evento')->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant_casa_angel')->dropIfExists('eventos');
        Schema::connection('tenant_casa_angel')->dropIfExists('tematicas');
        Schema::connection('tenant_casa_angel')->dropIfExists('ocasiones');
    }
};
