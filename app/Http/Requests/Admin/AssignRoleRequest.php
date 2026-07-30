<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for assigning a role to an existing user.
 * Used by UserController::assignRole().
 */
class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'in:hr,admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Please select a role to assign.',
            'role.in'       => 'The selected role is invalid.',
        ];
    }
}
