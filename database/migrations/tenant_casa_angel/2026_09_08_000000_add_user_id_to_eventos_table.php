<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant_casa_angel')->table('eventos', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant_casa_angel')->table('eventos', function (Blueprint $table): void {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};