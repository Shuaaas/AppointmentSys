<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * All roles can view the appointment list/table.
     * Manager sees it read-only; enforcement of "read-only" happens
     * in update()/create()/delete() below, not here.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, Role::values());
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->isHr()) {
            return (int) ($appointment->user_id ?? 0) === (int) $user->id;
        }

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
     * Full-record update. Records role is deliberately excluded here —
     * they get a separate, narrower permission below (updateTransactionNumber).
     */
    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->isHr()) {
            return (int) ($appointment->user_id ?? 0) === (int) $user->id;
        }

        return $user->isAdmin();
    }

    /**
     * HR role: can ONLY edit the transaction_number field.
     */
    public function updateTransactionNumber(User $user, Appointment $appointment): bool
    {
        return $user->isHr() || $user->isAdmin();
    }

    /**
     * Set Date of Signing and archive.
     */
    public function setDateOfSigning(User $user, Appointment $appointment): bool
    {
        if ($user->isHr()) {
            return (int) ($appointment->user_id ?? 0) === (int) $user->id;
        }

        return $user->isAdmin();
    }

    public function print(User $user, Appointment $appointment): bool
    {
        if ($user->isHr()) {
            return (int) ($appointment->user_id ?? 0) === (int) $user->id;
        }

        return $user->isAdmin();
    }

    public function archive(User $user, Appointment $appointment): bool
    {
        if ($user->isHr()) {
            return (int) ($appointment->user_id ?? 0) === (int) $user->id;
        }

        return $user->isAdmin();
    }

    /**
     * Only Admin can hard-delete. Consider soft-deletes (deleted_at)
     * even for Admin, so nothing is unrecoverable.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Trash bin: restoring a soft-deleted record, or permanently
     * destroying it. Kept as locked-down as delete() itself.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin();
    }

    /**
     * Manager gets NO write access at all — explicit deny-all as a safety net
     * in case any of the above ever gets misconfigured to include 'manager'.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isManager() && $ability !== 'view' && $ability !== 'viewAny') {
            return false;
        }

        return null; // fall through to the specific method above
    }
}