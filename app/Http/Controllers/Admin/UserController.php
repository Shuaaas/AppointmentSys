<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::where(function ($query) {
            $query->where('is_active', true)
                ->orWhereNull('requested_role');
        })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:hr,records,manager,admin'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'requested_role' => $data['role'],
            'is_active' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully and sent for approval.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function assignRole(Request $request, User $user)
    {
        $this->authorize('assignRole', $user);

        $data = $request->validate([
            'role' => ['required', 'in:hr,records,manager,admin'],
        ]);

        $user->role = $data['role'];
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User role updated successfully.');
    }

    public function activate(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $update = ['is_active' => true, 'requested_role' => null];

        if ($user->role === 'records' && $user->requested_role && $user->requested_role !== 'records') {
            $update['role'] = $user->requested_role;
        }

        $user->update($update);

        return redirect()->back()->with('success', 'User account activated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($user->is_active) {
            abort(403, 'Only pending accounts can be deleted from this screen.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pending account deleted successfully.');
    }

    public function deactivate(Request $request, User $user)
    {
        $this->authorize('deactivate', $user);

        $user->update(['is_active' => false]);

        return redirect()->back()->with('success', 'User account deactivated successfully.');
    }
}
