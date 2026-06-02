<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profits', function (Blueprint $table) {
            $table->foreignId('source_payment_id')->nullable()->after('transaction_id')->constrained('payments')->nullOnDelete();
            $table->foreignId('source_user_id')->nullable()->after('source_payment_id')->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('source_level')->nullable()->after('source_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('profits', function (Blueprint $table) {
            $table->dropForeign(['source_payment_id']);
            $table->dropForeign(['source_user_id']);
            $table->dropColumn(['source_payment_id', 'source_user_id', 'source_level']);
        });
    }
};
