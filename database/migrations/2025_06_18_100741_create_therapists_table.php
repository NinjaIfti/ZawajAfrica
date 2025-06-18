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
        Schema::create('therapists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->json('specializations'); // Array of specialization areas
            $table->string('degree');
            $table->integer('years_of_experience');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->json('availability'); // Array of available days/times
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('languages')->nullable(); // Comma separated languages
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapists');
    }
};
