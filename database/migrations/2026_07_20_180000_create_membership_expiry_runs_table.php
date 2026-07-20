<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_expiry_runs', function (Blueprint $table): void {
            $table->id();
            $table->dateTime('run_at')->index();
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('downgraded')->default(0);
            $table->unsignedInteger('free_renewals')->default(0);
            $table->json('downgraded_user_ids')->nullable();
            $table->json('free_renewal_user_ids')->nullable();
            $table->unsignedInteger('whatsapp_group_removed')->default(0);
            $table->unsignedInteger('telegram_banned')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_expiry_runs');
    }
};
