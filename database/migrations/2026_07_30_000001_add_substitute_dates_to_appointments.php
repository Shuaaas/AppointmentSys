<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('substitute_from')->nullable()->after('publication_date_to');
            $table->date('substitute_to')->nullable()->after('substitute_from');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['substitute_from', 'substitute_to']);
        });
    }
};
