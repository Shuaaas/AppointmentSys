<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Centralizes all user management business logic.
 * Extracted from UserController so the controller only handles HTTP concerns.
 */
class UserService
{
    /**
     * Create a user account directly (Admin "Add User" form).
     * Account is immediately active — no approval step.
     */
    public function createDirect(array $data): User
    {
        return User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'role'           => $data['role'],
            'requested_role' => null,
            'is_active'      => true,
        ]);
    }

    /**
     * Create a pending user account (requires Admin approval before login).
     */
    public function createPending(array $data): User
    {
        return User::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'role'           => $data['role'],
            'requested_role' => $data['role'],
            'is_active'      => false,
        ]);
    }

    /**
     * Update a user's name, email, and optionally their password.
     */
    public function update(User $user, array $data): User
    {
        $user->name  = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }

    /**
     * Assign a new role to a user.
     */
    public function assignRole(User $user, string $role): User
    {
        $user->role = $role;
        $user->save();

        return $user;
    }

    /**
     * Activate a pending user account.
     */
    public function activate(User $user): User
    {
        $user->update(['is_active' => true, 'requested_role' => null]);

        return $user;
    }

    /**
     * Deactivate an active user account.
     */
    public function deactivate(User $user): User
    {
        $user->update(['is_active' => false]);

        return $user;
    }

    /**
     * Reset a user's password without requiring their current one (Admin privilege).
     */
    public function resetPassword(User $user, string $newPassword): User
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return $user;
    }
}
