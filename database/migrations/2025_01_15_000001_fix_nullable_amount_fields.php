<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This historical migration predates the affected table definitions in a
     * clean checkout. Existing installations still receive the intended repair;
     * fresh installations are completed by the later reconciliation migration.
     */
    public function up(): void
    {
        if (Schema::hasTable('therapist_bookings')) {
            if (Schema::hasColumn('therapist_bookings', 'amount')) {
                DB::table('therapist_bookings')->whereNull('amount')->update(['amount' => 0]);
                Schema::table('therapist_bookings', function (Blueprint $table) {
                    $table->decimal('amount', 10, 2)->default(0)->change();
                });
            }

            if (!Schema::hasColumn('therapist_bookings', 'payment_status')) {
                Schema::table('therapist_bookings', function (Blueprint $table) {
                    $table->string('payment_status')->default('pending');
                });
            }
        }

        if (Schema::hasTable('therapists') && Schema::hasColumn('therapists', 'hourly_rate')) {
            DB::table('therapists')->whereNull('hourly_rate')->update(['hourly_rate' => 0]);
            Schema::table('therapists', function (Blueprint $table) {
                $table->decimal('hourly_rate', 8, 2)->default(0)->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('therapist_bookings') && Schema::hasColumn('therapist_bookings', 'amount')) {
            Schema::table('therapist_bookings', function (Blueprint $table) {
                $table->decimal('amount', 10, 2)->nullable()->change();
            });
        }

        if (Schema::hasTable('therapists') && Schema::hasColumn('therapists', 'hourly_rate')) {
            Schema::table('therapists', function (Blueprint $table) {
                $table->decimal('hourly_rate', 8, 2)->nullable()->change();
            });
        }
    }
};
