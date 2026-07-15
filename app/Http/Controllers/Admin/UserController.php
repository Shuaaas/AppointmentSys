<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddUserRequest;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * List all active/non-pending users (excluding the current Admin).
     * Policy: Admin only.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::where('id', '!=', auth()->id())
            ->where(fn ($q) => $q->where('is_active', true)->orWhereNull('requested_role'))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the "Add User" form.
     * Policy: Admin only.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    /**
     * Create a user account directly (active immediately, no approval step).
     * Policy: Admin only.
     */
    public function addUser(AddUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = $this->userService->createDirect($request->validated());

        return redirect()
            ->route('admin.users.create')
            ->with('success', "{$user->name} was successfully added as {$user->roleLabel()}.");
    }

    /**
     * Create a pending user account (requires Admin approval before login).
     * Policy: Admin only.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->userService->createPending($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully and sent for approval.');
    }

    /**
     * Update a user's name, email, and optionally password.
     * Policy: Admin only.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Assign a role to a user.
     * Policy: Admin only.
     */
    public function assignRole(AssignRoleRequest $request, User $user): RedirectResponse
    {
        $this->authorize('assignRole', $user);

        $this->userService->assignRole($user, $request->validated()['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User role updated successfully.');
    }

    /**
     * Activate a pending user account.
     * Policy: Admin only.
     */
    public function activate(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $this->userService->activate($user);

        return redirect()->back()
            ->with('success', 'User account activated successfully.');
    }

    /**
     * Deactivate an active user account.
     * Policy: Admin only (cannot deactivate own account).
     */
    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $this->userService->deactivate($user);

        return redirect()->back()
            ->with('success', 'User account deactivated successfully.');
    }

    /**
     * Delete a pending (inactive) user account.
     * Policy: Admin only; only pending accounts may be deleted from this screen.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($user->is_active) {
            abort(403, 'Only pending accounts can be deleted from this screen.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Pending account deleted successfully.');
    }

    /**
     * List all users for password reset.
     * Policy: Admin only.
     */
    public function passwords(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::orderBy('name')->get();

        return view('admin.passwords.index', compact('users'));
    }

    /**
     * Reset another user's password without requiring their current one.
     * Policy: Admin only.
     */
    public function resetPassword(ResetPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->userService->resetPassword($user, $request->validated()['password']);

        return redirect()
            ->route('admin.passwords.index')
            ->with('success', "Password for {$user->name} was reset successfully.");
    }
}
