<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('multimedia', 'preview_url')) {
            return;
        }

        Schema::table('multimedia', function (Blueprint $table) {
            $table->string('preview_url')->nullable()->after('url');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('multimedia', 'preview_url')) {
            return;
        }

        Schema::table('multimedia', function (Blueprint $table) {
            $table->dropColumn('preview_url');
        });
    }
};
