<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `record_state` ENUM('active','concluded','deleted') NOT NULL DEFAULT 'active'");
        }

        DB::table('appointments')
            ->whereNotNull('deleted_at')
            ->update(['record_state' => 'deleted']);
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `record_state` ENUM('active','concluded') NOT NULL DEFAULT 'active'");
        }
    }
};
