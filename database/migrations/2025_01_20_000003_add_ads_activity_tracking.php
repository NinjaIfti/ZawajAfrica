<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'ads_viewed' to the valid activity types in UserTierService
        // Update the user_daily_activities table to support ads tracking
        
        // Insert some sample ads viewing activities if needed
        DB::statement("
            ALTER TABLE user_daily_activities 
            COMMENT = 'Tracks daily user activities including profile_views, messages_sent, likes_sent, matches_created, profile_updates, ads_viewed'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the comment
        DB::statement("
            ALTER TABLE user_daily_activities 
            COMMENT = ''
        ");
    }
}; 