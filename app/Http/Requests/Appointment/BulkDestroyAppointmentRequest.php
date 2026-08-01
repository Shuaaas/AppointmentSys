<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for bulk soft-deleting appointments.
 * Used by AppointmentController::bulkDestroy().
 */
class BulkDestroyAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isHr() || $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:appointments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'No appointments were selected.',
            'ids.min'      => 'Please select at least one appointment.',
            'ids.*.exists' => 'One or more selected appointments do not exist.',
        ];
    }
}
