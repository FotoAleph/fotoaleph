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
        Schema::table('redes_sociales', function (Blueprint $table) {
            $table->foreignId('social_network_type_id')->nullable()->constrained('social_network_types');
            $table->dropColumn(['name', 'icon']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redes_sociales', function (Blueprint $table) {
            $table->dropForeign(['social_network_type_id']);
            $table->dropColumn('social_network_type_id');
            $table->string('name');
            $table->string('icon')->nullable();
        });
    }
};
