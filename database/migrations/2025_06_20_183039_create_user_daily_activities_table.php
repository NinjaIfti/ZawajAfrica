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
        Schema::create('user_daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity'); // 'profile_views', 'messages_sent', 'likes_sent', 'matches_created', 'profile_updates'
            $table->date('date');
            $table->integer('count')->default(1);
            $table->timestamps();
            
            // Composite index for fast lookups
            $table->unique(['user_id', 'activity', 'date'], 'user_activity_date_unique');
            $table->index(['date', 'activity'], 'activity_date_index');
            $table->index('user_id', 'user_id_index');
        });
        
        // Add table comment for documentation
        DB::statement("ALTER TABLE user_daily_activities COMMENT 'Tracks daily user activities for tier-based limit enforcement'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_activities');
    }
};
