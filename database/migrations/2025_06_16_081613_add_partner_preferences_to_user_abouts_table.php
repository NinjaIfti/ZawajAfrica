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
        Schema::table('user_abouts', function (Blueprint $table) {
            $table->string('looking_for_age_min')->nullable();
            $table->string('looking_for_age_max')->nullable();
            $table->string('looking_for_education')->nullable();
            $table->string('looking_for_religious_practice')->nullable();
            $table->string('looking_for_marital_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_abouts', function (Blueprint $table) {
            $table->dropColumn([
                'looking_for_age_min',
                'looking_for_age_max',
                'looking_for_education',
                'looking_for_religious_practice',
                'looking_for_marital_status',
            ]);
        });
    }
};
