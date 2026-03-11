<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'Aetsas01@gmail.com'],
            [
                'name' => 'Esteban Rivera',
                'password' => env('ADMIN_DEFAULT_PASSWORD', 'Admin12345*'),
                'commission_balance' => 0,
                'approved_at' => now(),
            ]
        );

        if ($admin->sponsor_id !== $admin->id) {
            $admin->sponsor_id = $admin->id;
        }

        if (Schema::hasColumn('users', 'affiliate_code')) {
            $admin->affiliate_code = User::buildAffiliateCode($admin->name, $admin->id);
        }

        $admin->save();

        $admin->assignRole('admin');
    }
}
