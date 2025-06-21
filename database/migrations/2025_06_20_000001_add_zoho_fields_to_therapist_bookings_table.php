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
        Schema::table('therapist_bookings', function (Blueprint $table) {
            $table->string('zoho_booking_id')->nullable()->after('meeting_link');
            $table->json('zoho_data')->nullable()->after('zoho_booking_id');
            $table->timestamp('zoho_last_sync')->nullable()->after('zoho_data');
            $table->string('platform')->nullable()->after('session_type'); // Google Meet, WhatsApp, Zoom
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_bookings', function (Blueprint $table) {
            $table->dropColumn(['zoho_booking_id', 'zoho_data', 'zoho_last_sync', 'platform']);
        });
    }
}; 