<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `record_state` ENUM('active','in_progress','completed','concluded','deleted') NOT NULL DEFAULT 'active'");
        }

        DB::table('appointments')
            ->where('record_state', 'new')
            ->update(['record_state' => 'active']);
    }

    public function down(): void
    {
        DB::table('appointments')
            ->whereIn('record_state', ['in_progress', 'completed'])
            ->update(['record_state' => 'active']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `record_state` ENUM('active','concluded','deleted') NOT NULL DEFAULT 'active'");
        }
    }
};
