<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant_biotek')->hasTable('multimedia')) {
            Schema::connection('tenant_biotek')->create('multimedia', function (Blueprint $table) {
                $table->id();
                $table->string('url');
                $table->string('preview_url')->nullable();
                $table->string('type')->default('image');
                $table->string('mime_type')->nullable();
                $table->timestamps();
            });
            
        }

        if(!Schema::connection('tenant_biotek')->hasTable('talleres')){
            Schema::connection('tenant_biotek')->create('talleres', function (Blueprint $table) {
                $table->id();
                $table->date('fecha');
                $table->unsignedInteger('duration_seconds');
                $table->string('codigo')->unique();
                $table->timestamps();
            });
        }
        if(!Schema::connection('tenant_biotek')->hasTable('preguntas')){
            Schema::connection('tenant_biotek')->create('preguntas', function (Blueprint $table) {
                $table->id();
                $table->text('texto');
                $table->string('tipo')->default('seleccion_unica');
                $table->unsignedInteger('nivel')->default(1);
                $table->timestamps();
            });
        }
        if(!Schema::connection('tenant_biotek')->hasTable('opciones')){
            Schema::connection('tenant_biotek')->create('opciones', function (Blueprint $table) {
                $table->id();
                $table->text('texto');
                $table->boolean('es_correcta')->default(false);
                $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
                $table->timestamps();
            });
        }
        if(!Schema::connection('tenant_biotek')->hasTable('intentos')){
            Schema::connection('tenant_biotek')->create('intentos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('taller_id')->constrained('talleres')->onDelete('cascade');
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->unsignedInteger('intento')->default(1);
                $table->timestamps();
            });
        }

        if(!Schema::connection('tenant_biotek')->hasTable('respuestas')){
            Schema::connection('tenant_biotek')->create('respuestas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
                $table->foreignId('opcion_id')->constrained('opciones')->onDelete('cascade');
                $table->foreignId('intento_id')->constrained('intentos')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_biotek')->hasTable('estudiantes')) {
            Schema::connection('tenant_biotek')->create('estudiantes', function (Blueprint $table) {
                $table->id();
                $table->string('nombres');
                $table->string('apellidos');
                $table->string('identificacion')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_biotek')->hasTable('estudiante_multimedia')) {
            Schema::connection('tenant_biotek')->create('estudiante_multimedia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_biotek')->hasTable('estudiantes_talleres')) {
            Schema::connection('tenant_biotek')->create('estudiantes_talleres', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->foreignId('taller_id')->constrained('talleres')->onDelete('cascade');
                $table->timestamps();
            });
        }
        if (! Schema::connection('tenant_biotek')->hasTable('carnets')) {
            Schema::connection('tenant_biotek')->create('carnets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->date('fecha_expedicion')->nullable();
                $table->date('fecha_vencimiento')->nullable();
                $table->string('numero')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('tenant_biotek')->dropIfExists('estudiantes_talleres');
        Schema::connection('tenant_biotek')->dropIfExists('estudiante_multimedia');
        Schema::connection('tenant_biotek')->dropIfExists('estudiantes');
        Schema::connection('tenant_biotek')->dropIfExists('talleres');
        Schema::connection('tenant_biotek')->dropIfExists('multimedia');
        Schema::connection('tenant_biotek')->dropIfExists('carnets');
        Schema::connection('tenant_biotek')->dropIfExists('respuestas');
        Schema::connection('tenant_biotek')->dropIfExists('intentos');
        Schema::connection('tenant_biotek')->dropIfExists('opciones');
        Schema::connection('tenant_biotek')->dropIfExists('preguntas');
    }
};
