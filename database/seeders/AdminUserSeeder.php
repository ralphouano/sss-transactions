<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sss.gov'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
