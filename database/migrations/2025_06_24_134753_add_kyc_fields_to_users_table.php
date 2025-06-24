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
            // Monnify KYC fields
            $table->string('bvn', 11)->nullable()->index();
            $table->string('nin', 11)->nullable()->index();
            $table->string('monnify_account_reference')->nullable()->unique();
            $table->json('monnify_reserved_accounts')->nullable(); // Store all reserved account details
            $table->enum('kyc_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->timestamp('kyc_verified_at')->nullable();
            $table->text('kyc_failure_reason')->nullable();
            $table->boolean('kyc_bvn_verified')->default(false);
            $table->boolean('kyc_nin_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bvn',
                'nin', 
                'monnify_account_reference',
                'monnify_reserved_accounts',
                'kyc_status',
                'kyc_verified_at',
                'kyc_failure_reason',
                'kyc_bvn_verified',
                'kyc_nin_verified'
            ]);
        });
    }
};
