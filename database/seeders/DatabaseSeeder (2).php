<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * By default, this runs v1 seeding for basic functionality.
     * Use specific seeders for different API versions:
     * - php artisan db:seed --class=V1DatabaseSeeder (basic data)
     * - php artisan db:seed --class=V2DatabaseSeeder (with payment system)
     */
    public function run(): void
    {
        $this->command->info('🌱 Running default database seeding...');
        $this->command->line('');

        // Default to v1 seeding for backward compatibility
        $this->call([
            V1DatabaseSeeder::class,
        ]);

        $this->command->line('');
        $this->command->info('💡 Seeding Options:');
        $this->command->line('- For API v1 only: php artisan db:seed --class=V1DatabaseSeeder');
        $this->command->line('- For API v2 (with payments): php artisan db:seed --class=V2DatabaseSeeder');
        $this->command->line('- For payments only: php artisan db:seed --class=PaymentSeeder');
        $this->command->line('');
        $this->command->warn('Note: V2 seeding includes all v1 data plus payment system data.');
    }
}
