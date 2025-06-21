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
        Schema::create('user_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who sent the like - preserve history
            $table->foreignId('liked_user_id')->nullable()->constrained('users')->onDelete('set null'); // Who was liked - preserve history
            $table->enum('status', ['pending', 'matched', 'passed'])->default('pending');
            $table->timestamp('liked_at')->useCurrent();
            $table->timestamps();
            
            // Prevent duplicate likes between the same users
            $table->unique(['user_id', 'liked_user_id']);
            
            // Indexes for faster queries
            $table->index(['user_id', 'status']);
            $table->index(['liked_user_id', 'status']);
            $table->index('liked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_likes');
    }
};
