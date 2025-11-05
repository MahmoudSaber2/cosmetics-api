<?php

namespace Database\Seeders;

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
        $products = [
            // Foundations
            [
                'name' => 'Flawless Foundation',
                'description' => 'Full coverage liquid foundation with 24-hour wear. Perfect for all skin types.',
                'brand' => 'GlamourPro',
                'type' => 'foundation',
                'color' => 'Ivory',
                'size' => '30ml',
                'gender' => 'women',
                'price' => 45.99,
                'status' => 'active',
                'stock_quantity' => 50,
                'min_stock_level' => 10,
            ],
            [
                'name' => 'Natural Glow Foundation',
                'description' => 'Lightweight foundation with natural finish and SPF 15 protection.',
                'brand' => 'BeautyEssentials',
                'type' => 'foundation',
                'color' => 'Beige',
                'size' => '30ml',
                'gender' => 'women',
                'price' => 38.50,
                'status' => 'active',
                'stock_quantity' => 35,
                'min_stock_level' => 8,
            ],
            [
                'name' => 'Matte Perfection Foundation',
                'description' => 'Oil-free matte foundation for oily and combination skin types.',
                'brand' => 'PureMatte',
                'type' => 'foundation',
                'color' => 'Sand',
                'size' => '25ml',
                'gender' => 'women',
                'price' => 42.00,
                'status' => 'active',
                'stock_quantity' => 28,
                'min_stock_level' => 5,
            ],

            // Lipsticks
            [
                'name' => 'Velvet Matte Lipstick',
                'description' => 'Long-lasting matte lipstick with intense color payoff.',
                'brand' => 'LuxeLips',
                'type' => 'lipstick',
                'color' => 'Ruby Red',
                'size' => '3.5g',
                'gender' => 'women',
                'price' => 24.99,
                'status' => 'active',
                'stock_quantity' => 75,
                'min_stock_level' => 15,
            ],
            [
                'name' => 'Glossy Shine Lipstick',
                'description' => 'Moisturizing lipstick with high-shine finish and vitamin E.',
                'brand' => 'GlossyGlow',
                'type' => 'lipstick',
                'color' => 'green Coral',
                'size' => '4g',
                'gender' => 'women',
                'price' => 19.99,
                'status' => 'active',
                'stock_quantity' => 60,
                'min_stock_level' => 12,
            ],
            [
                'name' => 'Classic Red Lipstick',
                'description' => 'Timeless red lipstick with creamy texture and rich pigmentation.',
                'brand' => 'ClassicBeauty',
                'type' => 'lipstick',
                'color' => 'Classic Red',
                'size' => '3.8g',
                'gender' => 'women',
                'price' => 29.50,
                'status' => 'active',
                'stock_quantity' => 45,
                'min_stock_level' => 10,
            ],

            // Perfumes
            [
                'name' => 'Midnight Elegance',
                'description' => 'Sophisticated evening fragrance with notes of jasmine, vanilla, and musk.',
                'brand' => 'ElegantScents',
                'type' => 'perfume',
                'color' => null,
                'size' => '50ml',
                'gender' => 'women',
                'price' => 89.99,
                'status' => 'active',
                'stock_quantity' => 25,
                'min_stock_level' => 5,
            ],
            [
                'name' => 'Fresh Breeze',
                'description' => 'Light and refreshing daytime fragrance with citrus and floral notes.',
                'brand' => 'FreshScents',
                'type' => 'perfume',
                'color' => null,
                'size' => '30ml',
                'gender' => 'women',
                'price' => 65.00,
                'status' => 'active',
                'stock_quantity' => 40,
                'min_stock_level' => 8,
            ],
            [
                'name' => 'Urban Legend',
                'description' => 'Bold masculine fragrance with woody and spicy undertones.',
                'brand' => 'UrbanScents',
                'type' => 'perfume',
                'color' => null,
                'size' => '75ml',
                'gender' => 'men',
                'price' => 95.00,
                'status' => 'active',
                'stock_quantity' => 30,
                'min_stock_level' => 6,
            ],

            // Mascaras
            [
                'name' => 'Volume Max Mascara',
                'description' => 'Dramatic volume mascara for fuller, thicker-looking lashes.',
                'brand' => 'LashPerfect',
                'type' => 'mascara',
                'color' => 'Black',
                'size' => '10ml',
                'gender' => 'women',
                'price' => 22.99,
                'status' => 'active',
                'stock_quantity' => 55,
                'min_stock_level' => 12,
            ],
            [
                'name' => 'Waterproof Mascara',
                'description' => 'Long-lasting waterproof mascara perfect for all-day wear.',
                'brand' => 'AquaLash',
                'type' => 'mascara',
                'color' => 'Brown',
                'size' => '8ml',
                'gender' => 'women',
                'price' => 26.50,
                'status' => 'active',
                'stock_quantity' => 42,
                'min_stock_level' => 10,
            ],

            // Eyeshadows
            [
                'name' => 'Smoky Eyes Palette',
                'description' => '12-shade eyeshadow palette perfect for creating smoky eye looks.',
                'brand' => 'EyeArt',
                'type' => 'eyeshadow',
                'color' => 'Neutral Tones',
                'size' => '15g',
                'gender' => 'women',
                'price' => 39.99,
                'status' => 'active',
                'stock_quantity' => 32,
                'min_stock_level' => 8,
            ],
            [
                'name' => 'Shimmer Eyeshadow',
                'description' => 'Single shimmer eyeshadow with high-impact metallic finish.',
                'brand' => 'ShimmerGlow',
                'type' => 'eyeshadow',
                'color' => 'Gold',
                'size' => '2g',
                'gender' => 'women',
                'price' => 15.99,
                'status' => 'active',
                'stock_quantity' => 68,
                'min_stock_level' => 15,
            ],

            // Blushes
            [
                'name' => 'Natural Flush Blush',
                'description' => 'Buildable powder blush for a natural, healthy glow.',
                'brand' => 'NaturalGlow',
                'type' => 'blush',
                'color' => 'Peach',
                'size' => '5g',
                'gender' => 'women',
                'price' => 18.99,
                'status' => 'active',
                'stock_quantity' => 48,
                'min_stock_level' => 10,
            ],
            [
                'name' => 'Cream Blush',
                'description' => 'Blendable cream blush for a natural, dewy finish.',
                'brand' => 'CreamyGlow',
                'type' => 'blush',
                'color' => 'Rose',
                'size' => '4g',
                'gender' => 'women',
                'price' => 21.50,
                'status' => 'active',
                'stock_quantity' => 35,
                'min_stock_level' => 8,
            ],

            // Men's Products
            [
                'name' => 'Beard Oil Premium',
                'description' => 'Nourishing beard oil with natural oils for soft, manageable facial hair.',
                'brand' => 'BeardCare',
                'type' => 'beard oil',
                'color' => null,
                'size' => '30ml',
                'gender' => 'men',
                'price' => 32.99,
                'status' => 'active',
                'stock_quantity' => 25,
                'min_stock_level' => 5,
            ],
            [
                'name' => 'Aftershave Balm',
                'description' => 'Soothing aftershave balm with aloe vera and vitamin E.',
                'brand' => 'SmoothShave',
                'type' => 'aftershave',
                'color' => null,
                'size' => '100ml',
                'gender' => 'men',
                'price' => 24.99,
                'status' => 'active',
                'stock_quantity' => 38,
                'min_stock_level' => 8,
            ],

            // Unisex Products
            [
                'name' => 'Hydrating Face Serum',
                'description' => 'Intensive hydrating serum with hyaluronic acid for all skin types.',
                'brand' => 'SkinCare Plus',
                'type' => 'serum',
                'color' => null,
                'size' => '30ml',
                'gender' => 'unisex',
                'price' => 55.00,
                'status' => 'active',
                'stock_quantity' => 22,
                'min_stock_level' => 5,
            ],
            [
                'name' => 'Vitamin C Serum',
                'description' => 'Brightening vitamin C serum for radiant, even-toned skin.',
                'brand' => 'VitaGlow',
                'type' => 'serum',
                'color' => null,
                'size' => '20ml',
                'gender' => 'unisex',
                'price' => 48.99,
                'status' => 'active',
                'stock_quantity' => 28,
                'min_stock_level' => 6,
            ],
            [
                'name' => 'Gentle Cleanser',
                'description' => 'Mild daily cleanser suitable for sensitive skin.',
                'brand' => 'GentleCare',
                'type' => 'cleanser',
                'color' => null,
                'size' => '150ml',
                'gender' => 'unisex',
                'price' => 19.99,
                'status' => 'active',
                'stock_quantity' => 45,
                'min_stock_level' => 10,
            ],
        ];

        foreach ($products as $productData) {
            $stockQuantity = $productData['stock_quantity'];
            $minStockLevel = $productData['min_stock_level'];

            unset($productData['stock_quantity'], $productData['min_stock_level']);

            $product = Product::create($productData);

            // Create inventory record
            Inventory::create([
                'product_id' => $product->id,
                'stock_quantity' => $stockQuantity,
                'min_stock_level' => $minStockLevel,
            ]);
        }
    }
}
