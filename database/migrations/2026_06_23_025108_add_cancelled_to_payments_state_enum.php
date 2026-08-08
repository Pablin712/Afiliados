<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-only syntax (ENUM MODIFY). SQLite (used by the local/test suite)
        // has no real ENUM type, so `state` is already a free-form column there.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE payments MODIFY COLUMN state ENUM('approved','rejected','pending','cancelled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("UPDATE payments SET state = 'rejected' WHERE state = 'cancelled'");
        DB::statement("ALTER TABLE payments MODIFY COLUMN state ENUM('approved','rejected','pending') NOT NULL DEFAULT 'pending'");
    }
};
