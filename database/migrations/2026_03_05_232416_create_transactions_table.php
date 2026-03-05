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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['income', 'expense'])->index();
            $table->decimal('amount_previous', 14, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('amount_now', 14, 2)->default(0);
            $table->text('detail')->nullable();
            $table->boolean('is_annulled')->default(false)->index();
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
