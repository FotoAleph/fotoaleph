<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant_jym')->hasTable('grupos')) {
            Schema::connection('tenant_jym')->create('grupos', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('descripcion')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::connection('tenant_jym')->hasTable('proyectos')) {
            if (! Schema::connection('tenant_jym')->hasColumn('proyectos', 'grupo_id')) {
                Schema::connection('tenant_jym')->table('proyectos', function (Blueprint $table) {
                    $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete()->after('categoria_id');
                });
            }

            if (! Schema::connection('tenant_jym')->hasColumn('proyectos', 'publicar_en_vitrina')) {
                Schema::connection('tenant_jym')->table('proyectos', function (Blueprint $table) {
                    $table->boolean('publicar_en_vitrina')->default(false)->after('descripcion');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::connection('tenant_jym')->hasTable('proyectos')) {
            if (Schema::connection('tenant_jym')->hasColumn('proyectos', 'grupo_id')) {
                Schema::connection('tenant_jym')->table('proyectos', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('grupo_id');
                });
            }

            if (Schema::connection('tenant_jym')->hasColumn('proyectos', 'publicar_en_vitrina')) {
                Schema::connection('tenant_jym')->table('proyectos', function (Blueprint $table) {
                    $table->dropColumn('publicar_en_vitrina');
                });
            }
        }

        Schema::connection('tenant_jym')->dropIfExists('grupos');
    }
};