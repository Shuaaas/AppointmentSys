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
            DB::statement("ALTER TABLE `appointments` MODIFY `nature_of_appointment` ENUM('Original','Promotion','Transfer','Reappointment','Reinstatement','Demotion','Re-Classification','Re-Employment','Re-Appointment') NOT NULL");
        }

        // 2. Map old values to their new equivalents (now allowed by the superset enum).
        DB::table('appointments')
            ->where('nature_of_appointment', 'Reappointment')
            ->update(['nature_of_appointment' => 'Re-Appointment']);
        DB::table('appointments')
            ->where('nature_of_appointment', 'Reinstatement')
            ->update(['nature_of_appointment' => 'Re-Employment']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // 3. Narrow down to the final new enum (all current data is now valid).
            DB::statement("ALTER TABLE `appointments` MODIFY `nature_of_appointment` ENUM('Original','Promotion','Demotion','Transfer','Re-Classification','Re-Employment','Re-Appointment') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `nature_of_appointment` ENUM('Original','Promotion','Transfer','Reappointment','Reinstatement','Demotion','Re-Classification','Re-Employment','Re-Appointment') NOT NULL");
        }

        DB::table('appointments')
            ->where('nature_of_appointment', 'Re-Appointment')
            ->update(['nature_of_appointment' => 'Reappointment']);
        DB::table('appointments')
            ->where('nature_of_appointment', 'Re-Employment')
            ->update(['nature_of_appointment' => 'Reinstatement']);
        DB::table('appointments')
            ->where('nature_of_appointment', 'Demotion')
            ->update(['nature_of_appointment' => 'Promotion']);
        DB::table('appointments')
            ->where('nature_of_appointment', 'Re-Classification')
            ->update(['nature_of_appointment' => 'Promotion']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `appointments` MODIFY `nature_of_appointment` ENUM('Original','Promotion','Transfer','Reappointment','Reinstatement') NOT NULL");
        }
    }
};
