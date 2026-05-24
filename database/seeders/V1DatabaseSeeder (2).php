<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class V1DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database for API v1.
     * This includes basic data needed for v1 functionality.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding data for API v1...');

        $this->call([
            // Core system data
            RolePermissionSeeder::class,
            AdminUserSeeder::class,

            // Basic catalog data
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,

            // Basic client data (optional)
            // ClientSeeder::class,

            // Basic order data (optional)
            // OrderSeeder::class,
        ]);

        $this->command->info('✅ API v1 seeding completed successfully!');
        $this->command->line('');
        $this->command->line('Available data:');
        $this->command->line('- Admin users with roles and permissions');
        $this->command->line('- Product catalog (brands, categories, products)');
        $this->command->line('- Basic system configuration');
        $this->command->line('');
        $this->command->warn('Note: Client and Order seeders are commented out by default.');
        $this->command->warn('Uncomment them if you need sample data for testing.');
    }
}
