<?php

namespace Database\Seeders;

use App\Models\MembershipType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $customerType = MembershipType::query()->where('name', 'customer')->first();

        if ($customerType === null) {
            $this->command->warn('MembershipType "customer" not found. Run MembershipTypeSeeder first.');
            return;
        }

        $now = now();

        DB::table('programs')->updateOrInsert(
            ['name' => 'AET Trader Academy'],
            [
                'description'        => 'Formación completa en trading: análisis técnico, gestión de riesgo y psicología del trader. Acceso a mentoría, materiales y comunidad por 2 meses.',
                'membership_type_id' => $customerType->id,
                'first_payment_cost' => 147.00,
                'renewal_cost'       => 97.00,
                'duration_months'    => 2,
                'is_active'          => true,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]
        );
    }
}
