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
        Schema::create('user_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->nullable()->constrained('users')->onDelete('set null'); // Preserve match history
            $table->foreignId('user2_id')->nullable()->constrained('users')->onDelete('set null'); // Preserve match history
            $table->timestamp('matched_at')->useCurrent();
            $table->enum('status', ['active', 'unmatched', 'blocked'])->default('active');
            $table->text('conversation_starter')->nullable(); // AI-generated conversation starter
            $table->json('compatibility_details')->nullable(); // Store compatibility analysis
            $table->timestamps();
            
            // Ensure user1_id is always smaller than user2_id for consistency
            $table->unique(['user1_id', 'user2_id']);
            
            // Indexes for faster queries
            $table->index(['user1_id', 'status']);
            $table->index(['user2_id', 'status']);
            $table->index('matched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_matches');
    }
};
