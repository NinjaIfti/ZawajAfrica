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
            $table->date('booking_date')->nullable()->after('appointment_datetime');
            $table->string('booking_time')->nullable()->after('booking_date');
            
            $table->decimal('amount', 10, 2)->nullable()->after('notes');
            $table->string('payment_reference')->nullable()->after('amount');
            $table->string('payment_status')->default('pending')->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_date', 'booking_time', 'notes', 'amount', 'payment_reference', 'payment_status']);
        });
    }
};
