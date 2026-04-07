<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('tenant_sport_bogota')->hasTable('multimediable')) {
            return;
        }

        if (! Schema::connection('tenant_sport_bogota')->hasTable('estudiante_multimedia')) {
            Schema::connection('tenant_sport_bogota')->create('estudiante_multimedia', function (Blueprint $table) {
                $table->id();
                $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->timestamps();
            });
        }

        $targetHasRows = DB::connection('tenant_sport_bogota')->table('estudiante_multimedia')->exists();

        if (! $targetHasRows) {
            $rows = DB::connection('tenant_sport_bogota')
                ->table('multimediable')
                ->select(['multimediable_id', 'multimedia_id', 'created_at', 'updated_at'])
                ->get()
                ->map(fn ($row) => [
                    'estudiante_id' => $row->multimediable_id,
                    'multimedia_id' => $row->multimedia_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])
                ->all();

            if ($rows !== []) {
                DB::connection('tenant_sport_bogota')->table('estudiante_multimedia')->insert($rows);
            }
        }

        Schema::connection('tenant_sport_bogota')->drop('multimediable');
    }

    public function down(): void
    {
        if (! Schema::connection('tenant_sport_bogota')->hasTable('estudiante_multimedia')) {
            return;
        }

        if (! Schema::connection('tenant_sport_bogota')->hasTable('multimediable')) {
            Schema::connection('tenant_sport_bogota')->create('multimediable', function (Blueprint $table) {
                $table->id();
                $table->string('multimediable_type');
                $table->unsignedBigInteger('multimediable_id');
                $table->foreignId('multimedia_id')->constrained('multimedia')->onDelete('cascade');
                $table->timestamps();
            });
        }

        $legacyHasRows = DB::connection('tenant_sport_bogota')->table('multimediable')->exists();

        if (! $legacyHasRows) {
            $rows = DB::connection('tenant_sport_bogota')
                ->table('estudiante_multimedia')
                ->select(['estudiante_id', 'multimedia_id', 'created_at', 'updated_at'])
                ->get()
                ->map(fn ($row) => [
                    'multimediable_type' => App\Models\Estudiante::class,
                    'multimediable_id' => $row->estudiante_id,
                    'multimedia_id' => $row->multimedia_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ])
                ->all();

            if ($rows !== []) {
                DB::connection('tenant_sport_bogota')->table('multimediable')->insert($rows);
            }
        }

        Schema::connection('tenant_sport_bogota')->drop('estudiante_multimedia');
    }
};