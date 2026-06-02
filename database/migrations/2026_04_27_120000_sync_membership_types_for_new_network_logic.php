<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $newCatalog = [
            ['name' => 'free', 'affiliates_required' => 0, 'cost' => 0, 'profit' => 0],
            ['name' => 'customer', 'affiliates_required' => 0, 'cost' => 97, 'profit' => 0],
            ['name' => 'beginner', 'affiliates_required' => 1, 'cost' => 0, 'profit' => 0],
            ['name' => 'constructor', 'affiliates_required' => 3, 'cost' => 0, 'profit' => 0],
            ['name' => 'explorer', 'affiliates_required' => 5, 'cost' => 0, 'profit' => 40],
            ['name' => 'professional', 'affiliates_required' => 8, 'cost' => 0, 'profit' => 100],
            ['name' => 'elite', 'affiliates_required' => 10, 'cost' => 0, 'profit' => 250],
            ['name' => 'master', 'affiliates_required' => 12, 'cost' => 0, 'profit' => 550],
            ['name' => 'legend', 'affiliates_required' => 15, 'cost' => 0, 'profit' => 1100],
        ];

        DB::transaction(function () use ($newCatalog, $now): void {
            $professionalId = DB::table('membership_types')->where('name', 'professional')->value('id');
            $proffesionalId = DB::table('membership_types')->where('name', 'proffesional')->value('id');

            // Normalize typo: "proffesional" -> "professional" keeping FK integrity.
            if ($proffesionalId !== null && $professionalId !== null && (int) $proffesionalId !== (int) $professionalId) {
                DB::table('memberships')
                    ->where('membership_type_id', (int) $proffesionalId)
                    ->update(['membership_type_id' => (int) $professionalId]);

                DB::table('programs')
                    ->where('membership_type_id', (int) $proffesionalId)
                    ->update(['membership_type_id' => (int) $professionalId]);

                DB::table('membership_types')
                    ->where('id', (int) $proffesionalId)
                    ->delete();
            } elseif ($proffesionalId !== null && $professionalId === null) {
                DB::table('membership_types')
                    ->where('id', (int) $proffesionalId)
                    ->update([
                        'name' => 'professional',
                        'updated_at' => $now,
                    ]);
            }

            foreach ($newCatalog as $row) {
                DB::table('membership_types')->updateOrInsert(
                    ['name' => $row['name']],
                    [
                        'affiliates_required' => $row['affiliates_required'],
                        'cost' => $row['cost'],
                        'profit' => $row['profit'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        $legacyCatalog = [
            ['name' => 'free', 'affiliates_required' => 0, 'cost' => 0, 'profit' => 0],
            ['name' => 'customer', 'affiliates_required' => 0, 'cost' => 97, 'profit' => 0],
            ['name' => 'beginner', 'affiliates_required' => 3, 'cost' => 0, 'profit' => 0],
            ['name' => 'explorer', 'affiliates_required' => 10, 'cost' => 0, 'profit' => 100],
            ['name' => 'professional', 'affiliates_required' => 20, 'cost' => 0, 'profit' => 200],
            ['name' => 'elite', 'affiliates_required' => 30, 'cost' => 0, 'profit' => 300],
        ];

        DB::transaction(function () use ($legacyCatalog, $now): void {
            foreach ($legacyCatalog as $row) {
                DB::table('membership_types')->updateOrInsert(
                    ['name' => $row['name']],
                    [
                        'affiliates_required' => $row['affiliates_required'],
                        'cost' => $row['cost'],
                        'profit' => $row['profit'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        });
    }
};
