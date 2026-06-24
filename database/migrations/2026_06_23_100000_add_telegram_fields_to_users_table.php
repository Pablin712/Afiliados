<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->bigInteger('telegram_chat_id')->nullable()->unique()->after('phone');
            $table->string('telegram_code', 12)->nullable()->unique()->after('telegram_chat_id');
        });

        // Generate unique codes for all existing users
        $users = \DB::table('users')->whereNull('telegram_code')->get(['id']);
        foreach ($users as $user) {
            do {
                $code = strtoupper(Str::random(10));
            } while (\DB::table('users')->where('telegram_code', $code)->exists());

            \DB::table('users')->where('id', $user->id)->update(['telegram_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['telegram_chat_id']);
            $table->dropUnique(['telegram_code']);
            $table->dropColumn(['telegram_chat_id', 'telegram_code']);
        });
    }
};
