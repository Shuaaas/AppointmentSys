<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for creating a user directly from the Admin "Add User" form.
 * The account is stored immediately with is_active = true — no approval step.
 * Used by UserController::addUser().
 */
class AddUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email', 'unique:invitations,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role'     => ['required', 'in:hr'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'The selected role is invalid. Admin cannot create another Admin from this form.',
        ];
    }
}
