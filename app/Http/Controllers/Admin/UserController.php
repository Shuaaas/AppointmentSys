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

        $users = User::where('id', '!=', auth()->id())
            ->where(function ($query) {
                $query->where('is_active', true)
                    ->orWhereNull('requested_role');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the "Add User" form. The Admin creates the account here; the role
     * is assigned later from the Manage Users screen, so this form has no
     * role field. The record is created immediately on submit.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Create a user account directly from the Admin "Add User" form.
     * The account is stored right away (no approval step) and is activated
     * immediately so the Admin does not need to activate it manually.
     */
    public function addUser(Request $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:hr,records,manager'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'requested_role' => null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.users.create')
            ->with('success', $user->name . ' was successfully added as ' . $user->roleLabel() . '.');
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
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

    /**
     * List all users so the Admin can reset a forgotten password.
     * Policy: Admin only (route middleware + viewAny authorization).
     */
    public function passwords()
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->get();

        return view('admin.passwords.index', compact('users'));
    }

    /**
     * Reset another user's password without requiring their current one.
     * Policy: Admin only (update authorization).
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()
            ->route('admin.passwords.index')
            ->with('success', "Password for {$user->name} was reset successfully.");
    }
}
