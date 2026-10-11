<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a user used to be blocked by their payments and commissions. Those
 * are the accounting history (income, bank balances, what sponsors earned),
 * so instead of going with the user they now stay behind without one and
 * show as "deleted user".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('profits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            // The user's bank accounts go with the user, so a paid commission
            // loses the account it was paid to as well.
            $table->dropForeign(['user_bank_id']);
        });

        Schema::table('profits', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('user_bank_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('user_bank_id')->references('id')->on('user_banks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Rows left by deleted users could not point anywhere once user_id is
        // required again, and they are accounting history: refuse rather than
        // delete them.
        if (DB::table('payments')->whereNull('user_id')->exists()
            || DB::table('profits')->whereNull('user_id')->orWhereNull('user_bank_id')->exists()) {
            throw new RuntimeException('Payments or profits of deleted users exist; this migration cannot be rolled back without losing them.');
        }

        Schema::table('profits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['user_bank_id']);
        });

        Schema::table('profits', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unsignedBigInteger('user_bank_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('user_bank_id')->references('id')->on('user_banks')->restrictOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
        });
    }
};
