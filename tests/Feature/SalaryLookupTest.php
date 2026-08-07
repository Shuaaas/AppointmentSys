<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaryLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_salary_lookup_returns_amount_and_words_for_grade_and_step(): void
    {
        $user = User::create([
            'name' => 'HR Officer',
            'email' => 'hr-test@example.com',
            'password' => bcrypt('password'),
            'role' => 'hr',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('appointments.salary', ['grade' => '11', 'step' => '1']));

        $response->assertOk()
            ->assertJsonStructure([
                'amount',
                'words',
            ])
            ->assertJsonPath('amount', '27800.00');
    }
}
