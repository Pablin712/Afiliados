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
        Schema::table('channels', function (Blueprint $table) {
            // Whether free members must be removed from this channel by the
            // weekly cleanup job (true = premium/exclusive group, false = open
            // to everyone and never touched by expulsion).
            $table->boolean('is_exclusive')->default(false)->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('is_exclusive');
        });
    }
};
