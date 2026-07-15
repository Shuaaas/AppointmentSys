<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for concluding/archiving an appointment.
 * Used by AppointmentController::conclude().
 */
class ConcludeAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by AppointmentPolicy::archive()
    }

    public function rules(): array
    {
        return [
            'conclusion_reason' => ['required', 'string', 'max:255'],
            'date_concluded'    => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'conclusion_reason.required' => 'Please provide a reason for concluding this appointment.',
            'date_concluded.required'    => 'Please provide the date of conclusion.',
            'date_concluded.date'        => 'The date of conclusion is not a valid date.',
        ];
    }
}
