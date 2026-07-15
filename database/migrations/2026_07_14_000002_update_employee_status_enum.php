<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // 1. Expand to a superset so existing old values remain valid during the transition.
            DB::statement("ALTER TABLE `appointments` MODIFY `employee_status` ENUM('Permanent','Temporary','Casual','Contractual','Coterminous','Substitute','Provisional') NOT NULL");
        }

        // 2. Map old values to their new equivalents (now allowed by the superset enum).
        DB::table('appointments')->where('employee_status', 'Temporary')->update(['employee_status' => 'Provisional']);
        DB::table('appointments')->where('employee_status', 'Casual')->update(['employee_status' => 'Provisional']);
        DB::table('appointments')->where('employee_status', 'Contractual')->update(['employee_status' => 'Provisional']);
        DB::table('appointments')->where('employee_status', 'Coterminous')->update(['employee_status' => 'Provisional']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // 3. Narrow down to the final new enum (all current data is now valid).
            DB::statement("ALTER TABLE `appointments` MODIFY `employee_status` ENUM('Permanent','Substitute','Provisional') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `employee_status` ENUM('Permanent','Temporary','Casual','Contractual','Coterminous','Substitute','Provisional') NOT NULL");
        }

        DB::table('appointments')->where('employee_status', 'Provisional')->update(['employee_status' => 'Temporary']);
        DB::table('appointments')->where('employee_status', 'Substitute')->update(['employee_status' => 'Temporary']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `employee_status` ENUM('Permanent','Temporary','Casual','Contractual','Coterminous') NOT NULL");
        }
    }
};
