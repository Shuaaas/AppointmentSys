<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('transaction_number')->nullable()->change();
        });

        if (! Schema::hasIndex('appointments', 'appointments_transaction_number_unique', 'unique')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unique('transaction_number', 'appointments_transaction_number_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_transaction_number_unique');
            $table->string('transaction_number')->nullable(false)->change();
            $table->unique('appointments_transaction_number_unique');
        });
    }
};
