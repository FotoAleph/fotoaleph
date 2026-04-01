<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant_sport_bogota')->hasTable('multimedia')) {
            Schema::connection('tenant_sport_bogota')->create('multimedia', function (Blueprint $table) {
                $table->id();
                $table->string('url')->index();
                $table->string('preview_url')->nullable();
                $table->string('type', 32)->index();
                $table->string('mime_type', 128)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('tenant_sport_bogota')->dropIfExists('multimedia');
    }
};
