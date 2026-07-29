<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE appointments MODIFY COLUMN position_from VARCHAR(150) NULL');
        DB::statement('ALTER TABLE appointments ADD COLUMN type_of_disability VARCHAR(255) NULL AFTER pwd');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE appointments MODIFY COLUMN position_from DATE NULL');
        DB::statement('ALTER TABLE appointments DROP COLUMN type_of_disability');
    }
};
