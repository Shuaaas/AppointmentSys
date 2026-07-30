<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for updating only the transaction number field.
 * Used by AppointmentController::updateTransactionNumber() — the single
 * narrow edit allowed for the HR role.
 */
class UpdateTransactionNumberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by AppointmentPolicy::updateTransactionNumber()
    }

    public function rules(): array
    {
        return [
            'transaction_number' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_number.required' => 'Please enter a transaction number.',
        ];
    }
}
