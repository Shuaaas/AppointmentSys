<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * All roles can view the appointment list/table.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, Role::values());
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return in_array($user->role, Role::values());
    }

    /**
     * Only HR and Admin can create a new appointment record.
     */
    public function create(User $user): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    /**
     * Full-record update. HR and Admin can update any appointment.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    /**
     * Set Date of Signing and archive.
     */
    public function setDateOfSigning(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    public function print(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    public function archive(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    /**
     * Only Admin can hard-delete. Consider soft-deletes (deleted_at)
     * even for Admin, so nothing is unrecoverable.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    /**
     * Trash bin: restoring a soft-deleted record, or permanently
     * destroying it. Kept as locked-down as delete() itself.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }
}