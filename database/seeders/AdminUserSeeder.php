<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => bcrypt('mans123456'),
                'role' => 'admin',
            ],
            [
                'name' => 'Store Manager',
                'email' => 'manager@cosmetics.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Inventory Manager',
                'email' => 'inventory@cosmetics.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
        ];

        foreach ($users as $userData) {
            \App\Models\User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
