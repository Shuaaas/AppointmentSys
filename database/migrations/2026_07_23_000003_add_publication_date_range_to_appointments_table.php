<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('publication_date_from')->nullable()->after('date_of_signing');
            $table->date('publication_date_to')->nullable()->after('publication_date_from');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['publication_date_from', 'publication_date_to']);
        });
    }
};
