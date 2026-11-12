<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Last request the user made while logged in, so the admin can see
            // how long an account has gone unused before deciding to delete it.
            // Sessions cannot tell: they are discarded two hours after the
            // last request.
            $table->timestamp('last_seen_at')->nullable()->after('approved_at')->index();
        });

        // The audit log already records every request, so history starts
        // from there instead of from today.
        DB::table('users')->update([
            'last_seen_at' => DB::raw('(SELECT MAX(actions.created_at) FROM actions WHERE actions.user_id = users.id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn('last_seen_at');
        });
    }
};
