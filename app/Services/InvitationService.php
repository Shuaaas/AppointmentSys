<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use Illuminate\Support\Str;

class InvitationService
{
    public function create(array $data): Invitation
    {
        $invitation = Invitation::create([
            'email'     => $data['email'],
            'name'      => $data['name'],
            'role'      => $data['role'],
            'password'  => bcrypt($data['password']),
            'token'     => Str::random(60),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return $invitation;
    }

    public function accept(string $token): User
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isValid()) {
            abort(403, 'This invitation is invalid or has expired.');
        }

        return $invitation->createUser();
    }
}
