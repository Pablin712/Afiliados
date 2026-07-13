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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['telegram', 'whatsapp']);
            $table->string('name');
            $table->string('purpose');
            $table->boolean('is_active')->default(true);
            // Telegram: numeric group chat id. WhatsApp: group JID (e.g. 1203...@g.us).
            $table->string('chat_id')->nullable();
            // Telegram bot token (per-channel override of the global bot).
            $table->text('bot_token')->nullable();
            // WhatsApp (Evolution API) connection details.
            $table->string('instance_name')->nullable();
            $table->string('server_url')->nullable();
            $table->text('api_key')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'purpose', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
