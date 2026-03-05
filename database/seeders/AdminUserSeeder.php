<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

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
            ]
        );

        if ($admin->sponsor_id !== $admin->id) {
            $admin->sponsor_id = $admin->id;
            $admin->save();
        }

        $admin->assignRole('admin');
    }
}
