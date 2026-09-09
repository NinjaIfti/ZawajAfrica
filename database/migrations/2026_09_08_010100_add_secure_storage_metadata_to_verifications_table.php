<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            // Existing rows remain on the public disk; all new uploads explicitly use kyc_private.
            $table->string('storage_disk', 64)->default('public')->after('back_image');
            $table->string('front_mime_type', 100)->nullable()->after('storage_disk');
            $table->unsignedBigInteger('front_size')->nullable()->after('front_mime_type');
            $table->string('back_mime_type', 100)->nullable()->after('front_size');
            $table->unsignedBigInteger('back_size')->nullable()->after('back_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropColumn([
                'storage_disk',
                'front_mime_type',
                'front_size',
                'back_mime_type',
                'back_size',
            ]);
        });
    }
};
