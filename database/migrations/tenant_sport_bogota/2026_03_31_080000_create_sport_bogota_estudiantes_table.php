<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        if (! Schema::connection('tenant_sport_bogota')->hasTable('estudiantes')) {
            Schema::connection('tenant_sport_bogota')->create('estudiantes', function (Blueprint $table) {
                $table->id();
                $table->string('categoria');
                $table->string('nombre');
                $table->foreignId('foto_url')->nullable()->constrained('multimedia')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('tenant_sport_bogota')->dropIfExists('estudiantes');
    }
};
