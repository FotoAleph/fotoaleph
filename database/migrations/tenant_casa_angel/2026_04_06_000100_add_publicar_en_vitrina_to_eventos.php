<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('tenant_casa_angel')->hasTable('eventos')
            && ! Schema::connection('tenant_casa_angel')->hasColumn('eventos', 'publicar_en_vitrina')) {
            Schema::connection('tenant_casa_angel')->table('eventos', function (Blueprint $table) {
                $table->boolean('publicar_en_vitrina')->default(false)->after('codigo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::connection('tenant_casa_angel')->hasTable('eventos')
            && Schema::connection('tenant_casa_angel')->hasColumn('eventos', 'publicar_en_vitrina')) {
            Schema::connection('tenant_casa_angel')->table('eventos', function (Blueprint $table) {
                $table->dropColumn('publicar_en_vitrina');
            });
        }
    }
};