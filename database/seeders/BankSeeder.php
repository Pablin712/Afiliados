<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'name' => 'Binance',
                'owner' => 'Esteban Rivera',
                'identification' => 'BINANCE-ADMIN-001',
                'number' => 'binance-pay-esteban',
                'amount' => 0,
                'detail' => 'Cuenta principal de Binance para pagos de registro.',
                'photo' => null,
            ],
            [
                'name' => 'Banco Pichincha',
                'owner' => 'Esteban Rivera',
                'identification' => 'PICHINCHA-ADMIN-001',
                'number' => '2200000001',
                'amount' => 0,
                'detail' => 'Cuenta principal de Banco Pichincha para pagos de registro.',
                'photo' => null,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('banks')->updateOrInsert(
                [
                    'name' => $row['name'],
                    'number' => $row['number'],
                ],
                [
                    'owner' => $row['owner'],
                    'identification' => $row['identification'],
                    'amount' => $row['amount'],
                    'detail' => $row['detail'],
                    'photo' => $row['photo'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
