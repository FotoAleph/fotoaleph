<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('tenant_biotek')->hasTable('talleres')
            && ! Schema::connection('tenant_biotek')->hasColumn('talleres', 'nombre')) {
            Schema::connection('tenant_biotek')->table('talleres', function (Blueprint $table) {
                $table->string('nombre')->default('Taller')->after('id');
            });
        }

        if (Schema::connection('tenant_biotek')->hasTable('estudiantes_talleres')) {
            foreach ([
                'pago' => 'taller_id',
                'abono' => 'pago',
                'debe' => 'abono',
                'saldo_total' => 'debe',
            ] as $column => $after) {
                if (! Schema::connection('tenant_biotek')->hasColumn('estudiantes_talleres', $column)) {
                    Schema::connection('tenant_biotek')->table('estudiantes_talleres', function (Blueprint $table) use ($column, $after) {
                        $table->decimal($column, 10, 2)->default(0)->after($after);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::connection('tenant_biotek')->hasTable('estudiantes_talleres')) {
            foreach (['saldo_total', 'debe', 'abono', 'pago'] as $column) {
                if (Schema::connection('tenant_biotek')->hasColumn('estudiantes_talleres', $column)) {
                    Schema::connection('tenant_biotek')->table('estudiantes_talleres', function (Blueprint $table) use ($column) {
                        $table->dropColumn($column);
                    });
                }
            }
        }

        if (Schema::connection('tenant_biotek')->hasTable('talleres')
            && Schema::connection('tenant_biotek')->hasColumn('talleres', 'nombre')) {
            Schema::connection('tenant_biotek')->table('talleres', function (Blueprint $table) {
                $table->dropColumn('nombre');
            });
        }
    }
};