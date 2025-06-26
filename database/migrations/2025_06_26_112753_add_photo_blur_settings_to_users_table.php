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
            $table->boolean('photos_blurred')->default(false)->after('profile_photo');
            $table->enum('photo_blur_mode', ['manual', 'auto_unlock'])->default('manual')->after('photos_blurred');
            $table->json('photo_blur_permissions')->nullable()->after('photo_blur_mode'); // For storing user IDs who can see unblurred photos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photos_blurred', 'photo_blur_mode', 'photo_blur_permissions']);
        });
    }
};
