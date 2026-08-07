<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        // Login view has ONLY email + password fields.
        // No role dropdown / role radio buttons / role hidden input — nothing.
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user instanceof User) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Unable to resolve the authenticated user.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact your Admin.',
            ]);
        }

        // Role is read from the authenticated user's DB record — never from
        // request input, a hidden form field, or anything client-supplied.
        if ($user->isHr()) {
            return redirect()->route('dashboard.index');
        }

        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}