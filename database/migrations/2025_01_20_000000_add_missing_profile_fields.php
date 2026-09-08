<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This historical repair predates the profile extension tables on a fresh
     * install. Apply it only where the tables already exist; a later
     * reconciliation migration handles clean installations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_backgrounds')) {
            Schema::table('user_backgrounds', function (Blueprint $table) {
                if (!Schema::hasColumn('user_backgrounds', 'ethnic_group')) {
                    $table->string('ethnic_group')->nullable()->after('nationality');
                }
                if (!Schema::hasColumn('user_backgrounds', 'islamic_affiliation')) {
                    $table->string('islamic_affiliation')->nullable()->after('ethnic_group');
                }
            });
        }

        if (Schema::hasTable('user_lifestyles') && !Schema::hasColumn('user_lifestyles', 'want_children')) {
            Schema::table('user_lifestyles', function (Blueprint $table) {
                $table->string('want_children')->nullable()->after('number_of_children');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_backgrounds')) {
            $columns = collect(['ethnic_group', 'islamic_affiliation'])
                ->filter(fn (string $column) => Schema::hasColumn('user_backgrounds', $column))
                ->values()
                ->all();

            if ($columns !== []) {
                Schema::table('user_backgrounds', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }

        if (Schema::hasTable('user_lifestyles') && Schema::hasColumn('user_lifestyles', 'want_children')) {
            Schema::table('user_lifestyles', fn (Blueprint $table) => $table->dropColumn('want_children'));
        }
    }
};
