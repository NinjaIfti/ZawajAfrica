<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('user_daily_activities')) {
            return;
        }

        DB::statement("ALTER TABLE user_daily_activities COMMENT = 'Tracks daily user activities including profile_views, messages_sent, likes_sent, matches_created, profile_updates, ads_viewed'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' && Schema::hasTable('user_daily_activities')) {
            DB::statement("ALTER TABLE user_daily_activities COMMENT = ''");
        }
    }
};
