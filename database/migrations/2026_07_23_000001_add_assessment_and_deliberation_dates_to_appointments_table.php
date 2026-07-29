<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('assessment_date')->nullable()->after('date_of_signing');
            $table->date('deliberation_date')->nullable()->after('assessment_date');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['assessment_date', 'deliberation_date']);
        });
    }
};
