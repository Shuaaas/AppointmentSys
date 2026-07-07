<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // What the applicant asked for at registration — informational only.
            // Admin decides the real `role` column value when approving the account.
            $table->string('requested_role')->nullable()->after('role');
        });

        // New self-registered accounts must start inactive until Admin approves.
        // (Your existing `role` migration defaulted is_active to true — that's
        // fine for Admin-created accounts, but registration must override it.)
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('requested_role');
        });
    }
};