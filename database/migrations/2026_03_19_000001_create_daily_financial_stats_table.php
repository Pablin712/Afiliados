<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_financial_stats', function (Blueprint $table) {
            $table->id();
            $table->date('stat_date')->unique();
            $table->decimal('incomes_total', 14, 2)->default(0);
            $table->decimal('expenses_total', 14, 2)->default(0);
            $table->decimal('net_profit', 14, 2)->default(0);
            $table->unsignedInteger('new_users_count')->default(0);
            $table->unsignedInteger('new_customers_count')->default(0);
            $table->unsignedInteger('approved_payments_count')->default(0);
            $table->decimal('pending_profits_total', 14, 2)->default(0);
            $table->decimal('profits_paid_total', 14, 2)->default(0);
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_financial_stats');
    }
};
