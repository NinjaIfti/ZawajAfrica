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
        // Add missing fields to user_backgrounds table
        Schema::table('user_backgrounds', function (Blueprint $table) {
            $table->string('ethnic_group')->nullable()->after('nationality');
            $table->string('islamic_affiliation')->nullable()->after('ethnic_group');
        });
        
        // Add missing fields to user_lifestyles table  
        Schema::table('user_lifestyles', function (Blueprint $table) {
            $table->string('want_children')->nullable()->after('number_of_children');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_backgrounds', function (Blueprint $table) {
            $table->dropColumn(['ethnic_group', 'islamic_affiliation']);
        });
        
        Schema::table('user_lifestyles', function (Blueprint $table) {
            $table->dropColumn('want_children');
        });
    }
}; 