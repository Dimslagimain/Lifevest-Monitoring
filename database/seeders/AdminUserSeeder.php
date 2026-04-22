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
        // Superadmin
        User::updateOrCreate(
            ['email' => 'superadmin@tnp.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('superadmintnp'),
                'role' => User::ROLE_SUPERADMIN,
            ]
        );

        // Admin (Alternative)
        User::updateOrCreate(
            ['email' => 'admin@tnp.com'],
            [
                'name' => 'Admin TNP 1',
                'password' => Hash::make('admintnp'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin2@tnp.com'],
            [
                'name' => 'Admin TNP 2',
                'password' => Hash::make('admintnp'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin3@tnp.com'],
            [
                'name' => 'Admin TNP 3',
                'password' => Hash::make('admintnp'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Regular User
        User::updateOrCreate(
            ['email' => 'user@tnp.com'],
            [
                'name' => 'User TNP',
                'password' => Hash::make('usertnp'),
                'role' => User::ROLE_USER,
            ]
        );
    }
}
