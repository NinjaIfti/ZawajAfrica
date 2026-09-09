<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('member')->index();
            $table->string('status', 32)->default('active')->index();
            $table->text('bvn_encrypted')->nullable();
            $table->text('nin_encrypted')->nullable();
            $table->text('monnify_account_reference_encrypted')->nullable();
            $table->text('monnify_reserved_accounts_encrypted')->nullable();
        });

        DB::table('users')
            ->select(['id', 'bvn', 'nin', 'monnify_account_reference', 'monnify_reserved_accounts'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $updates = [];

                    if (! empty($user->bvn)) {
                        $updates['bvn_encrypted'] = Crypt::encryptString($user->bvn);
                    }

                    if (! empty($user->nin)) {
                        $updates['nin_encrypted'] = Crypt::encryptString($user->nin);
                    }

                    if (! empty($user->monnify_account_reference)) {
                        $updates['monnify_account_reference_encrypted'] = Crypt::encryptString($user->monnify_account_reference);
                    }

                    if (! empty($user->monnify_reserved_accounts)) {
                        $decoded = json_decode($user->monnify_reserved_accounts, true);
                        $updates['monnify_reserved_accounts_encrypted'] = Crypt::encryptString(json_encode(
                            is_array($decoded) ? $decoded : $user->monnify_reserved_accounts,
                            JSON_THROW_ON_ERROR
                        ));
                    }

                    if ($updates !== []) {
                        DB::table('users')->where('id', $user->id)->update($updates);
                    }
                }
            });

        if (config('product.legacy_admin.enabled') && filled(config('product.legacy_admin.email'))) {
            DB::table('users')
                ->where('email', config('product.legacy_admin.email'))
                ->where('role', 'member')
                ->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'role',
                'status',
                'bvn_encrypted',
                'nin_encrypted',
                'monnify_account_reference_encrypted',
                'monnify_reserved_accounts_encrypted',
            ]);
        });
    }
};
