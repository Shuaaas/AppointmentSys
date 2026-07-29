<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use App\Models\Appointment;
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }
     /**
     * Role helper methods — use the Role Enum instead of comparing strings directly.
     */
    public function isHr(): bool
    {
        return $this->role === Role::Hr->value;
    }
 
    public function isRecords(): bool
    {
        return $this->role === Role::Records->value;
    }
 
    public function isManager(): bool
    {
        return $this->role === Role::Manager->value;
    }
 
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin->value;
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
        return Role::tryFrom($this->role)?->label() ?? ucfirst((string) $this->role);
    }

    public function profileLabel(): string
    {
        return $this->displayName() . ' | ' . $this->roleLabel();
    }
 
    /**
     * Where to send this user after login.
     * Delegates to the Role Enum to keep routing logic in one place.
     */
    public function dashboardRoute(): string
    {
        return Role::tryFrom($this->role)?->dashboardRoute() ?? 'login';
    }
}
