<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::connection('tenant_jym')->hasTable('categorias')) {
            Schema::connection('tenant_jym')->create('categorias', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_jym')->hasTable('proyectos')) {
            Schema::connection('tenant_jym')->create('proyectos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_jym')->hasTable('multimedia')) {
            Schema::connection('tenant_jym')->create('multimedia', function (Blueprint $table) {
                $table->id();
                $table->string('url');
                $table->string('preview_url')->nullable();
                $table->string('type')->default('image');
                $table->string('mime_type')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_jym')->hasTable('multimediable')) {
            Schema::connection('tenant_jym')->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->morphs('multimediable');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('tenant_jym')->dropIfExists('multimediable');
        Schema::connection('tenant_jym')->dropIfExists('proyectos');
        Schema::connection('tenant_jym')->dropIfExists('multimedia');
        Schema::connection('tenant_jym')->dropIfExists('categorias');
    }
};
