<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for creating or fully updating an Appointment record.
 * Used by AppointmentController::store() and AppointmentController::update().
 */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by AppointmentPolicy
    }

    public function rules(): array
    {
        return [
            // Personal information
            'last_name'                 => ['required', 'string', 'max:100'],
            'first_name'                => ['required', 'string', 'max:100'],
            'middle_name'               => ['nullable', 'string', 'max:100'],
            'extension_name'            => ['nullable', 'string', 'max:20'],
            'sex'                       => ['nullable', 'in:Male,Female,Prefer not to say'],
            'date_of_birth'             => ['nullable', 'date', 'before:today'],
            'tin'                       => ['nullable', 'string', 'max:20'],
            'pwd'                       => ['nullable', 'in:Yes,No'],
            'type_of_disability'        => ['nullable', 'string', 'max:255'],
            'ip_group_member'           => ['nullable', 'in:Yes,No'],
            'ethnicity'                 => ['nullable', 'string', 'max:100'],

            // Position and salary
            'position_title'            => ['required', 'string', 'max:150'],
            'position_from'             => ['nullable', 'string', 'max:150'],
            'position_to'               => ['nullable', 'date', 'after_or_equal:position_from'],
            'salary_grade'              => ['nullable', 'string', 'max:10'],
            'salary_grade_step'         => ['nullable', 'string', 'max:10'],
            'monthly_salary'            => ['nullable', 'numeric', 'min:0'],
            'employee_status'           => ['required', 'in:Permanent,Substitute,Provisional'],
            'compensation_words'        => ['nullable', 'string', 'max:255'],
            'compensation_numbers'      => ['nullable', 'numeric', 'min:0'],
            'nature_of_appointment'     => ['required', 'in:Original,Promotion,Demotion,Transfer,Re-Classification,Re-Employment,Re-Appointment'],
            'reason'                    => ['nullable', 'string', 'max:255'],
            'position_level'            => ['nullable', 'in:First Level,Second Level,Third Level'],
            'appointment_status'        => ['nullable', 'in:Original,Renewal,Reappointment'],

            // Agency and administrative
            'department'                => ['nullable', 'string', 'max:255'],
            'school_district'           => ['nullable', 'string', 'max:150'],
            'school'                    => ['nullable', 'string', 'max:255'],
            'sector'                    => ['nullable', 'string', 'max:100'],
            'agency_name'               => ['nullable', 'string', 'max:255'],
            'plantilla_item_number'     => ['nullable', 'string', 'max:100'],
            'plantilla_page_number'     => ['nullable', 'string', 'max:20'],
            'odc_number'                => ['nullable', 'string', 'max:50'],
            'date_received_records'     => ['nullable', 'date'],
            'date_received_hr'          => ['nullable', 'date'],
            'previous_incumbent'        => ['nullable', 'string', 'max:150'],
            'incumbent'                 => ['nullable', 'string', 'max:150'],
            'publication_mode'          => ['nullable', 'in:CSC Bulletin,Agency Bulletin,Newspaper,Online,Not applicable'],
            'natural_vacancy'           => ['nullable', 'string', 'max:150'],

            // Eligibility and history
            'eligibility_type'          => ['nullable', 'string', 'max:150'],
            'eligibility_validity'      => ['nullable', 'date'],
            'eligibility_first_used'    => ['nullable', 'in:Yes,No'],
            'date_original_appointment' => ['nullable', 'date'],
            'date_last_promotion'       => ['nullable', 'date'],
            'date_of_signing'           => ['nullable', 'date'],
            'publication_date_from'     => ['nullable', 'date'],
            'publication_date_to'       => ['nullable', 'date', 'after_or_equal:publication_date_from'],
            'substitute_from'           => ['nullable', 'date'],
            'substitute_to'             => ['nullable', 'date', 'after_or_equal:substitute_from'],
            'assessment_date'           => ['nullable', 'date'],
            'deliberation_date'         => ['nullable', 'date'],
            'education'                 => ['nullable', 'string', 'max:255'],
            'senior_high_school'        => ['nullable', 'in:Yes,No'],
            'senior_high_strand'        => ['nullable', 'string', 'max:50'],
            'teaching_level'            => ['nullable', 'in:Elementary,Secondary,Not Applicable'],
            'non_teaching'              => ['nullable', 'in:Yes,No'],
            'encoding_personnel'        => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required'              => 'Please enter the last name.',
            'first_name.required'             => 'Please enter the first name.',
            'position_title.required'         => 'Please enter the position title.',
            'employee_status.required'        => 'Please select an employee status.',
            'nature_of_appointment.required'  => 'Please select the nature of appointment.',
        ];
    }
}
