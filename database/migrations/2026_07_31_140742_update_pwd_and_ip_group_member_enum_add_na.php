<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `appointments` MODIFY `pwd` ENUM('Yes','No','N/A') NOT NULL DEFAULT 'No'");
            DB::statement("ALTER TABLE `appointments` MODIFY `ip_group_member` ENUM('Yes','No','N/A') NOT NULL DEFAULT 'No'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE `appointments` MODIFY `pwd` ENUM('Yes','No') NOT NULL DEFAULT 'No'");
            DB::statement("ALTER TABLE `appointments` MODIFY `ip_group_member` ENUM('Yes','No') NOT NULL DEFAULT 'No'");
        }
    }
};
