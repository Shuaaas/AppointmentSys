<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InvitationController extends Controller
{
    public function __construct(private readonly InvitationService $invitationService) {}

    public function show(string $token): View
    {
        $invitation = Invitation::where('token', $token)->first();

        return view('invitation.accept', [
            'invitation' => $invitation,
        ]);
    }

    public function accept(string $token, InvitationService $invitationService): RedirectResponse
    {
        $user = $invitationService->accept($token);

        return redirect()->route('login')
            ->with('success', "Account for {$user->name} has been created. You can now sign in.");
    }
}
