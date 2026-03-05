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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('sponsor_id')->nullable()->after('remember_token')->constrained('users')->restrictOnDelete();
            $table->decimal('commission_balance', 12, 2)->default(0)->after('sponsor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sponsor_id');
            $table->dropColumn('commission_balance');
        });
    }
};
