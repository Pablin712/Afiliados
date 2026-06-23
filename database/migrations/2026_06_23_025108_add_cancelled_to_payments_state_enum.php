<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN state ENUM('approved','rejected','pending','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET state = 'rejected' WHERE state = 'cancelled'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN state ENUM('approved','rejected','pending') NOT NULL DEFAULT 'pending'");
    }
};
