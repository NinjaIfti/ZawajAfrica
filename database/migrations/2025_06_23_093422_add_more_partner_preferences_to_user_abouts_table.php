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
            $table->string('looking_for_relocate')->nullable();
            $table->string('looking_for_children')->nullable();
            $table->string('looking_for_tribe')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_abouts', function (Blueprint $table) {
            $table->dropColumn([
                'looking_for_relocate',
                'looking_for_children',
                'looking_for_tribe',
            ]);
        });
    }
};
