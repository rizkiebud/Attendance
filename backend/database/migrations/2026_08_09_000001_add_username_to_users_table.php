<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add column only if it doesn't exist yet (idempotent, safe on drift)
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username')->nullable()->after('email');
            });
        }

        // Backfill existing users dari prefix email (only where username NULL/empty)
        DB::table('users')->orderBy('id')->each(function ($user) {
            if (empty($user->username)) {
                $username = strtolower(explode('@', $user->email)[0]);
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            }
        });

        // Make non-null + unique only if unique index not already present
        $indexExists = collect(DB::select('SHOW INDEX FROM users'))
            ->pluck('Column_name')
            ->contains('username');
        if (!$indexExists) {
            Schema::table('users', function ($table) {
                $table->string('username')->nullable(false)->unique()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->dropColumn('username');
            });
        }
    }
};