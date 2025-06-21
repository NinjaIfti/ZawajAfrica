<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->string('zoho_service_id')->nullable()->after('additional_info');
            $table->string('zoho_staff_id')->nullable()->after('zoho_service_id');
            $table->json('zoho_settings')->nullable()->after('zoho_staff_id');
            $table->timestamp('zoho_last_sync')->nullable()->after('zoho_settings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapists', function (Blueprint $table) {
            $table->dropColumn(['zoho_service_id', 'zoho_staff_id', 'zoho_settings', 'zoho_last_sync']);
        });
    }
}; 