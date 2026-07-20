<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plantilla_items')) {
            DB::statement('ALTER TABLE `plantilla_items` RENAME TO `data`');
        }

        if (Schema::hasTable('data') && Schema::hasColumn('data', 'item_number')) {
            DB::statement('ALTER TABLE `data` CHANGE `item_number` `data` VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('data') && Schema::hasColumn('data', 'data')) {
            DB::statement('ALTER TABLE `data` CHANGE `data` `item_number` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('data')) {
            DB::statement('ALTER TABLE `data` RENAME TO `plantilla_items`');
        }
    }
};
