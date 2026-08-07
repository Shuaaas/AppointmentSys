<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('transaction_number')->nullable()->change();
            });
        }

        if (! Schema::hasIndex('appointments', 'appointments_transaction_number_unique', 'unique')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->unique('transaction_number', 'appointments_transaction_number_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('appointments', 'appointments_transaction_number_unique', 'unique')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropUnique('appointments_transaction_number_unique');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('transaction_number')->nullable(false)->change();
            });
        }
    }
};
