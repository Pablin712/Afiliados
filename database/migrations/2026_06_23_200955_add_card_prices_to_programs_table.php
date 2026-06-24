<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            // Null = card payment not available for this program.
            $table->decimal('card_first_payment_cost', 12, 2)->nullable()->after('first_payment_cost');
            $table->decimal('card_renewal_cost', 12, 2)->nullable()->after('renewal_cost');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['card_first_payment_cost', 'card_renewal_cost']);
        });
    }
};
