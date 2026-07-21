<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->unsignedInteger('renewal_count')->default(0)->after('last_payment_id');
        });

        // Backfill from real payment history: a membership that's been paid for more than
        // once has renewed at least (approved payments - 1) times. This can't see past
        // free-renewals that never left a payment trail, but 0 is the safe default for
        // those anyway (treats them as "still first period", which only widens the lookback
        // window rather than narrowing it).
        DB::statement(<<<'SQL'
            UPDATE memberships m
            SET renewal_count = GREATEST(0, (
                SELECT COUNT(*) FROM payments p
                WHERE p.user_id = m.user_id AND p.state = 'approved'
            ) - 1)
        SQL);
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table): void {
            $table->dropColumn('renewal_count');
        });
    }
};
