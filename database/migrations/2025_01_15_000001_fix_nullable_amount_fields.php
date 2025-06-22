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
            // Make amount field non-nullable with default value
            $table->decimal('amount', 10, 2)->default(0)->change();
            
            // Add payment_status if it doesn't exist
            if (!Schema::hasColumn('therapist_bookings', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            }
        });

        Schema::table('therapists', function (Blueprint $table) {
            // Ensure hourly_rate is not nullable
            $table->decimal('hourly_rate', 8, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('therapist_bookings', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->change();
        });

        Schema::table('therapists', function (Blueprint $table) {
            $table->decimal('hourly_rate', 8, 2)->nullable()->change();
        });
    }
}; 