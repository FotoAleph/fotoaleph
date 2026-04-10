<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class VitrinaCurationSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('vitrinas') || ! Schema::hasTable('vitrina_items')) {
            return;
        }
    }
}