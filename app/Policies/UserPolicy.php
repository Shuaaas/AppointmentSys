<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function assignRole(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function deactivate(User $user, User $target): bool
    {
        // Prevent an Admin from deactivating their own account and locking themselves out.
        if ($user->id === $target->id) {
            return false;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }
}