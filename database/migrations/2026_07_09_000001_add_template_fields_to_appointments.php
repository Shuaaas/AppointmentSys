<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Appointment template additions
            $table->string('natural_vacancy')->nullable()->after('incumbent');
            $table->date('date_of_signing')->nullable()->after('natural_vacancy');

            // Checklist template additions
            $table->text('education')->nullable()->after('date_of_signing');
            $table->enum('senior_high_school', ['Yes', 'No'])->nullable()->after('education');
            $table->string('senior_high_strand')->nullable()->after('senior_high_school');

            // Final Deliberation template additions
            $table->enum('non_teaching', ['Yes', 'No'])->nullable()->after('senior_high_strand');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'natural_vacancy',
                'date_of_signing',
                'education',
                'senior_high_school',
                'senior_high_strand',
                'non_teaching',
            ]);
        });
    }
};
