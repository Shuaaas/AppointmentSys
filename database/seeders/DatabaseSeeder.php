<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::create([
            'name' => 'HR Officer',
            'email' => 'hr@example.com',
            'password' => Hash::make('change-this-immediately'),
            'role' => 'hr',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Initial Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('change-this-immediately'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
