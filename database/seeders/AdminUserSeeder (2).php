<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

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
                'phone' => '1234567890',
                'address' => 'Admin Address',
                'password' => 'mans123456',
                'spatie_role' => 'superAdmin',
                'status' => StatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Store Manager',
                'email' => 'manager@cosmetics.com',
                'phone' => '0987654321',
                'address' => 'Manager Address',
                'password' => 'password',
                'spatie_role' => 'superAdmin',
                'status' => StatusEnum::ACTIVE->value,
            ],
            [
                'name' => 'Inventory Manager',
                'email' => 'inventory@cosmetics.com',
                'password' => 'password',
                'spatie_role' => 'supervisor',
                'status' => StatusEnum::ACTIVE->value,
            ],
        ];

        foreach ($users as $userData) {
            $spatieRole = $userData['spatie_role'];
            unset($userData['spatie_role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Assign Spatie role
            if (!$user->hasRole($spatieRole)) {
                $user->assignRole($spatieRole);
            }
        }
    }
}
