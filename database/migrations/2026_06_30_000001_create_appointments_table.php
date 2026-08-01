<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();

            // ── Personal information ──
            $table->string('last_name');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('extension_name')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Prefer not to say'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('tin')->nullable();
            $table->enum('pwd', ['Yes', 'No', 'N/A'])->default('No');
            $table->enum('ip_group_member', ['Yes', 'No', 'N/A'])->default('No');
            $table->string('ethnicity')->nullable();

            // ── Position and salary ──
            $table->string('position_title');
            $table->date('position_from')->nullable();
            $table->date('position_to')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('salary_grade_step')->nullable();
            $table->decimal('monthly_salary', 12, 2)->nullable();
            $table->enum('employee_status', ['Permanent', 'Temporary', 'Casual', 'Contractual', 'Coterminous']);
            $table->string('compensation_words')->nullable();
            $table->decimal('compensation_numbers', 12, 2)->nullable();
            $table->enum('nature_of_appointment', ['Original', 'Promotion', 'Transfer', 'Reappointment', 'Reinstatement']);
            $table->string('reason')->nullable();
            $table->enum('position_level', ['First Level', 'Second Level', 'Third Level'])->nullable();
            $table->enum('appointment_status', ['Original', 'Renewal', 'Reappointment'])->nullable();

            // ── Agency and administrative ──
            $table->string('department')->nullable();
            $table->string('school_district')->nullable();
            $table->string('sector')->nullable();
            $table->string('agency_name')->nullable();
            $table->string('plantilla_item_number')->nullable();
            $table->string('plantilla_page_number')->nullable();
            $table->string('odc_number')->nullable();
            $table->date('date_received_records')->nullable();
            $table->date('date_received_hr')->nullable();
            $table->string('previous_incumbent')->nullable();
            $table->string('incumbent')->nullable();
            $table->enum('publication_mode', ['CSC Bulletin', 'Agency Bulletin', 'Newspaper', 'Online', 'Not applicable'])->nullable();

            // ── Eligibility and history ──
            $table->string('eligibility_type')->nullable();
            $table->date('eligibility_validity')->nullable();
            $table->enum('eligibility_first_used', ['Yes', 'No'])->nullable();
            $table->date('date_original_appointment')->nullable();
            $table->date('date_last_promotion')->nullable();

            // ── Lifecycle / archive workflow tracking ──
            $table->enum('record_state', ['new', 'in_progress', 'completed', 'archived', 'deleted'])->default('new');
            $table->string('conclusion_reason')->nullable(); // Retired, Resigned, Transferred, End of fixed term, etc.
            $table->date('date_concluded')->nullable();

            // ── Encoding metadata ──
            $table->string('encoding_personnel')->nullable();
            $table->timestamp('encoded_at')->useCurrent();
            $table->softDeletes(); // powers the "Trash" feature
            $table->timestamps();

            $table->index('encoded_at');
            $table->index('record_state');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};