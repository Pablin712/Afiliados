<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            // Telegram forum-group topic id (message_thread_id). Several channel
            // rows can now share the same chat_id (one per topic within the
            // same forum-enabled group) instead of needing a separate group.
            $table->unsignedInteger('message_thread_id')->nullable()->after('chat_id');
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('message_thread_id');
        });
    }
};
