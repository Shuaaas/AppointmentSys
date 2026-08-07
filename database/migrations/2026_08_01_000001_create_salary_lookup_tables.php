<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('salary_grades')) {
            Schema::create('salary_grades', function (Blueprint $table): void {
                $table->id();
                $table->integer('grade')->unique();
                $table->string('name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('salary_steps')) {
            Schema::create('salary_steps', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('salary_grade_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('step');
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();

                $table->unique(['salary_grade_id', 'step']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_steps');
        Schema::dropIfExists('salary_grades');
    }
};
