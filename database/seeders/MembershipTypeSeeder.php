<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
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

        foreach ($rows as $row) {
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
    }
}
