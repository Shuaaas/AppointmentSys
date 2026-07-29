<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'name',
        'role',
        'password',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function isValid(): bool
    {
        return is_null($this->used_at) && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function createUser(): User
    {
        $user = User::create([
            'name'           => $this->name,
            'email'          => $this->email,
            'password'       => $this->password,
            'role'           => $this->role,
            'requested_role' => $this->role,
            'is_active'      => true,
        ]);

        $this->markAsUsed();

        return $user;
    }
}
