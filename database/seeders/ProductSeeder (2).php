<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If you want to ensure brand & category exist
        Brand::factory()->count(5)->create();
        Category::factory()->count(5)->create();

        // Create 50 products
        Product::factory()->count(50)->create();
    }
}
