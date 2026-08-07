<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            if (Schema::hasColumn('appointments', 'position_from')) {
                Schema::table('appointments', function ($table) {
                    $table->string('position_from', 150)->nullable()->change();
                });
            }

            if (! Schema::hasColumn('appointments', 'type_of_disability')) {
                Schema::table('appointments', function ($table) {
                    $table->string('type_of_disability')->nullable();
                });
            }
        } else {
            DB::statement('ALTER TABLE appointments MODIFY COLUMN position_from VARCHAR(150) NULL');
            DB::statement('ALTER TABLE appointments ADD COLUMN type_of_disability VARCHAR(255) NULL AFTER pwd');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            if (Schema::hasColumn('appointments', 'type_of_disability')) {
                Schema::table('appointments', function ($table) {
                    $table->dropColumn('type_of_disability');
                });
            }

            if (Schema::hasColumn('appointments', 'position_from')) {
                Schema::table('appointments', function ($table) {
                    $table->date('position_from')->nullable()->change();
                });
            }
        } else {
            DB::statement('ALTER TABLE appointments MODIFY COLUMN position_from DATE NULL');
            DB::statement('ALTER TABLE appointments DROP COLUMN type_of_disability');
        }
    }
};
