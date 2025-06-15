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
        Schema::table('users', function (Blueprint $table) {
            // Drop the location column since we're using city, state, country instead
            if (Schema::hasColumn('users', 'location')) {
                $table->dropColumn('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add back the location column if needed
            if (!Schema::hasColumn('users', 'location')) {
                $table->string('location')->nullable()->after('password');
            }
        });
    }
};
