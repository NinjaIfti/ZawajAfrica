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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Personal Information
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();
            $table->string('height')->nullable();
            $table->string('body_type')->nullable();
            $table->string('ethnicity')->nullable();
            $table->string('religion')->nullable();
            $table->string('religious_values')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('children')->nullable();
            $table->string('want_children')->nullable();
            
            // Education & Career
            $table->string('education_level')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('occupation')->nullable();
            $table->string('income_range')->nullable();
            
            // Lifestyle & Habits
            $table->string('smoking')->nullable();
            $table->string('drinking')->nullable();
            $table->string('prayer_frequency')->nullable();
            $table->boolean('hijab_wearing')->nullable();
            $table->string('beard_type')->nullable();
            
            // Matching Preferences
            $table->string('age_preference_min')->nullable();
            $table->string('age_preference_max')->nullable();
            $table->text('about_match')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
