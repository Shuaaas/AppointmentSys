<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('afa_downloaded_at')->nullable()->after('encoded_at');
            $table->timestamp('checklist_downloaded_at')->nullable()->after('afa_downloaded_at');
            $table->timestamp('rai_downloaded_at')->nullable()->after('checklist_downloaded_at');
            $table->timestamp('final_deliberation_downloaded_at')->nullable()->after('rai_downloaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'afa_downloaded_at',
                'checklist_downloaded_at',
                'rai_downloaded_at',
                'final_deliberation_downloaded_at',
            ]);
        });
    }
};
