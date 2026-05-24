<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class V2DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database for API v2.
     * This includes all v1 data plus payment system data.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding data for API v2...');

        $this->call([
            // All v1 data first
            V1DatabaseSeeder::class,

            // V2 specific seeders
            PaymentSeeder::class,
            V2ClientSeeder::class,
            V2OrderSeeder::class,
        ]);

        $this->command->info('✅ API v2 seeding completed successfully!');
        $this->command->line('');
        $this->command->line('Available data:');
        $this->command->line('- All API v1 data');
        $this->command->line('- Sample payment records');
        $this->command->line('- Enhanced client data with payment preferences');
        $this->command->line('- Orders with payment integration');
        $this->command->line('- Payment system configuration');
        $this->command->line('');
        $this->command->info('🚀 Your API v2 with payment system is ready to use!');
    }
}
