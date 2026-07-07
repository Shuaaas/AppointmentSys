<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'requested_role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
     /**
     * Role helper methods — used everywhere instead of comparing
     * $user->role === 'admin' directly, so the string lives in one place.
     */
    public function isHr(): bool
    {
        return $this->role === 'hr';
    }
 
    public function isRecords(): bool
    {
        return $this->role === 'records';
    }
 
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }
 
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
 
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function displayName(): string
    {
        return mb_strtoupper($this->name);
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'hr' => 'HR Officer',
            'records' => 'Records Officer',
            'manager' => 'Manager',
            'admin' => 'Administrator',
            default => ucfirst((string) $this->role),
        };
    }

    public function profileLabel(): string
    {
        return $this->displayName() . ' | ' . $this->roleLabel();
    }
 
    /**
     * Where to send this user after login. Used by the LoginController.
     */
    public function dashboardRoute(): string
    {
        return match ($this->role) {
            'hr'      => 'dashboard.index',
            'records' => 'appointments.index',
            'manager' => 'dashboard.index',
            'admin'   => 'dashboard.index',
            default   => 'login',
        };
    }
}
