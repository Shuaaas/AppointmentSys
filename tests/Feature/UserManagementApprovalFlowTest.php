<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_management_excludes_pending_accounts_but_includes_deactivated_accounts(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'requested_role' => null,
            'is_active' => true,
        ]);

        $pendingUser = User::create([
            'name' => 'Pending User',
            'email' => 'pending@example.com',
            'password' => bcrypt('password123'),
            'role' => 'hr',
            'requested_role' => 'hr',
            'is_active' => false,
        ]);

        $deactivatedUser = User::create([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'role' => 'records',
            'requested_role' => null,
            'is_active' => false,
        ]);

        $activeUser = User::create([
            'name' => 'Active User',
            'email' => 'active@example.com',
            'password' => bcrypt('password123'),
            'role' => 'manager',
            'requested_role' => null,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($activeUser->name);
        $response->assertSee($deactivatedUser->name);
        $response->assertDontSee($pendingUser->name);
    }
}
