<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_page_shows_concluded_appointments_only(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'requested_role' => null,
            'is_active' => true,
        ]);

        $concludedAppointment = Appointment::create([
            'transaction_number' => 'TXN-1001',
            'last_name' => 'Santos',
            'first_name' => 'Maria',
            'position_title' => 'Teacher I',
            'employee_status' => 'Permanent',
            'nature_of_appointment' => 'Original',
            'record_state' => 'concluded',
            'date_concluded' => '2026-07-15',
        ]);

        $activeAppointment = Appointment::create([
            'transaction_number' => 'TXN-1002',
            'last_name' => 'Cruz',
            'first_name' => 'Jose',
            'position_title' => 'Teacher I',
            'employee_status' => 'Permanent',
            'nature_of_appointment' => 'Original',
            'record_state' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('history.index'));

        $response->assertOk();
        $response->assertSee($concludedAppointment->full_name);
        $response->assertDontSee($activeAppointment->full_name);
    }
}
