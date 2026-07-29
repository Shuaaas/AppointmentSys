<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InvitationMail extends Mailable
{
    public function __construct(private Invitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your account has been created — DepEd Cavite Appointment System',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.invitation',
            with: [
                'invitation' => $this->invitation,
                'acceptUrl'  => route('invitation.accept', $this->invitation->token),
            ],
        );
    }
}
