<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the payload for updating an existing user's profile.
 * Password is optional — leave blank to keep the current one.
 * Used by UserController::update().
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ];
    }
}
