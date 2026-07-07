<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            // This is a REQUEST only — it does not grant the role.
            // Admin reviews and assigns the real `role` value on approval.
            'requested_role' => ['required', 'in:hr,records,manager,admin'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'requested_role' => $data['requested_role'],
            'role' => $data['requested_role'],
            'is_active' => false,
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Your account request was submitted. An Admin must approve it before you can log in.');
    }
}