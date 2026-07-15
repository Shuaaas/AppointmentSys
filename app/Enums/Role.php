<?php

namespace App\Enums;

/**
 * System roles for PAMS users.
 * Use this Enum everywhere instead of bare string literals ('hr', 'admin', etc.)
 * to prevent typos and keep role definitions in one place.
 */
enum Role: string
{
    case Hr      = 'hr';
    case Records = 'records';
    case Manager = 'manager';
    case Admin   = 'admin';

    /**
     * Human-readable label for display purposes.
     */
    public function label(): string
    {
        return match ($this) {
            self::Hr      => 'HR Officer',
            self::Records => 'Records Officer',
            self::Manager => 'Manager',
            self::Admin   => 'Administrator',
        };
    }

    /**
     * The route name each role is redirected to after login.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Hr      => 'dashboard.index',
            self::Records => 'appointments.index',
            self::Manager => 'dashboard.index',
            self::Admin   => 'dashboard.index',
        };
    }

    /**
     * Returns all role values as a plain string array.
     * Useful for validation rules: Rule::in(Role::values())
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
