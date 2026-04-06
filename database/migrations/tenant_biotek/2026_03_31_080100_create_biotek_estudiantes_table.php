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
                $table->string('fecha');
                $table->text('duracion')->nullable();
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
        
        if (! Schema::connection('tenant_biotek')->hasTable('multimediable')) {
            Schema::connection('tenant_biotek')->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->morphs('multimediable');
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
    }

    public function down(): void
    {
        Schema::connection('tenant_biotek')->dropIfExists('estudiantes_talleres');
        Schema::connection('tenant_biotek')->dropIfExists('multimediable');
        Schema::connection('tenant_biotek')->dropIfExists('estudiantes');
        Schema::connection('tenant_biotek')->dropIfExists('talleres');
        Schema::connection('tenant_biotek')->dropIfExists('multimedia');
    }
};
