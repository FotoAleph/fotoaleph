<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vitrina_items')) {
            return;
        }

        Schema::create('vitrina_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vitrina_id')->constrained()->cascadeOnDelete();
            $table->foreignId('multimedia_id')->constrained('multimedia')->cascadeOnDelete();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_connection')->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('es_portada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vitrina_items');
    }
};
