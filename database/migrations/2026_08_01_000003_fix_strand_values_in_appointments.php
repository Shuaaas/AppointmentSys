<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('appointments')
            ->where('senior_high_strand', 'TVL Track')
            ->update(['senior_high_strand' => 'SHS - TVL Track']);

        DB::table('appointments')
            ->where('senior_high_strand', 'Sports Track')
            ->update(['senior_high_strand' => 'SHS - Sports Track']);
    }

    public function down(): void
    {
        DB::table('appointments')
            ->where('senior_high_strand', 'SHS - TVL Track')
            ->update(['senior_high_strand' => 'TVL Track']);

        DB::table('appointments')
            ->where('senior_high_strand', 'SHS - Sports Track')
            ->update(['senior_high_strand' => 'Sports Track']);
    }
};
