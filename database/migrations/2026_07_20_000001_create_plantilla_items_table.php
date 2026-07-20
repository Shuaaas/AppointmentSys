<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantilla_items', function (Blueprint $table) {
            $table->id();
            $table->string('level')->nullable();
            $table->string('school_id')->nullable();
            $table->string('school_name')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('item_number');
            $table->string('position')->nullable();
            $table->string('sex')->nullable();
            $table->string('eligibility')->nullable();
            $table->string('first_time_used_of_eligibility')->nullable();
            $table->string('position_level')->nullable();
            $table->string('nature_of_appointment')->nullable();
            $table->string('status_of_appointment')->nullable();
            $table->timestamps();

            $table->index('item_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_items');
    }
};
