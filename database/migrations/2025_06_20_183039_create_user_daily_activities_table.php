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
        Schema::create('user_daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('activity'); // 'profile_views', 'messages_sent', etc.
            $table->date('date');
            $table->integer('count')->default(1);
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries for the same user/activity/date
            $table->unique(['user_id', 'activity', 'date']);
            
            // Index for faster queries
            $table->index(['user_id', 'date']);
            $table->index(['activity', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_daily_activities');
    }
};
