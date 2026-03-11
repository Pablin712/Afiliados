<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('affiliate_code', 80)->nullable()->unique()->after('email');
            $table->dateTime('approved_at')->nullable()->after('commission_balance')->index();
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $baseName = (string) Str::of((string) $user->name)->trim()->before(' ');
                    $prefix = (string) Str::of($baseName)
                        ->ascii()
                        ->replace(' ', '')
                        ->replaceMatches('/[^A-Za-z0-9]/', '');

                    $affiliateCode = ($prefix !== '' ? $prefix : 'USER').str_pad((string) $user->id, 3, '0', STR_PAD_LEFT);

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'approved_at' => now(),
                            'affiliate_code' => $affiliateCode,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['affiliate_code']);
            $table->dropColumn('affiliate_code');
            $table->dropColumn('approved_at');
        });
    }
};
