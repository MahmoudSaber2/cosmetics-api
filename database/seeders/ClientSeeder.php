<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Emma Johnson',
                'email' => 'emma.johnson@email.com',
                'phone' => '+1-555-0123',
                'address' => '123 Beauty Lane, Apt 4B',
                'city' => 'New York',
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah.williams@email.com',
                'phone' => '+1-555-0124',
                'address' => '456 Glamour Street',
                'city' => 'Los Angeles',
            ],
            [
                'name' => 'Jessica Brown',
                'email' => 'jessica.brown@email.com',
                'phone' => '+1-555-0125',
                'address' => '789 Makeup Avenue',
                'city' => 'Chicago',
            ],
            [
                'name' => 'Ashley Davis',
                'email' => 'ashley.davis@email.com',
                'phone' => '+1-555-0126',
                'address' => '321 Cosmetics Boulevard',
                'city' => 'Miami',
            ],
            [
                'name' => 'Amanda Miller',
                'email' => 'amanda.miller@email.com',
                'phone' => '+1-555-0127',
                'address' => '654 Beauty Plaza, Suite 12',
                'city' => 'Seattle',
            ],
            [
                'name' => 'Michelle Wilson',
                'email' => 'michelle.wilson@email.com',
                'phone' => '+1-555-0128',
                'address' => '987 Skincare Drive',
                'city' => 'Denver',
            ],
            [
                'name' => 'Jennifer Moore',
                'email' => 'jennifer.moore@email.com',
                'phone' => '+1-555-0129',
                'address' => '147 Fragrance Way',
                'city' => 'Boston',
            ],
            [
                'name' => 'Lisa Taylor',
                'email' => 'lisa.taylor@email.com',
                'phone' => '+1-555-0130',
                'address' => '258 Lipstick Lane',
                'city' => 'San Francisco',
            ],
            [
                'name' => 'Rachel Anderson',
                'email' => 'rachel.anderson@email.com',
                'phone' => '+1-555-0131',
                'address' => '369 Foundation Street',
                'city' => 'Austin',
            ],
            [
                'name' => 'Stephanie Thomas',
                'email' => 'stephanie.thomas@email.com',
                'phone' => '+1-555-0132',
                'address' => '741 Mascara Road',
                'city' => 'Portland',
            ],
            [
                'name' => 'Michael Johnson',
                'email' => 'michael.johnson@email.com',
                'phone' => '+1-555-0133',
                'address' => '852 Grooming Street',
                'city' => 'Phoenix',
            ],
            [
                'name' => 'David Smith',
                'email' => 'david.smith@email.com',
                'phone' => '+1-555-0134',
                'address' => '963 Cologne Avenue',
                'city' => 'Dallas',
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james.wilson@email.com',
                'phone' => '+1-555-0135',
                'address' => '159 Beard Care Lane',
                'city' => 'Nashville',
            ],
            [
                'name' => 'Robert Brown',
                'email' => 'robert.brown@email.com',
                'phone' => '+1-555-0136',
                'address' => '357 Aftershave Boulevard',
                'city' => 'Atlanta',
            ],
            [
                'name' => 'Christopher Davis',
                'email' => 'christopher.davis@email.com',
                'phone' => '+1-555-0137',
                'address' => '468 Skincare Plaza',
                'city' => 'Las Vegas',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}
