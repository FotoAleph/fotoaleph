<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant_casa_angel')->hasTable('multimedia')) {
            Schema::connection('tenant_casa_angel')->create('multimedia', function (Blueprint $table) {
                $table->id();
                $table->string('url');
                $table->string('preview_url')->nullable();
                $table->string('type')->default('image');
                $table->string('mime_type')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::connection('tenant_casa_angel')->hasTable('multimediable')) {
            Schema::connection('tenant_casa_angel')->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->morphs('multimediable');
                $table->timestamps();
            });
        }
        
    }

    public function down(): void
    {
        Schema::connection('tenant_casa_angel')->dropIfExists('multimediable');
        Schema::connection('tenant_casa_angel')->dropIfExists('multimedia');
    }
};
