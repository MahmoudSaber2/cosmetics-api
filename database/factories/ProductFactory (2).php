<?php

namespace Database\Factories;

use App\Enums\ProductStatusEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);



        return [
            'name'        => $name,
            'description' => $this->faker->sentence(10),
            'slug'        => Str::slug($name),

            'brand_id'    => Brand::inRandomOrder()->first()?->id ?? Brand::factory(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),

            'cost'        => $this->faker->numberBetween(50, 200),
            'price'       => $this->faker->numberBetween(100, 500),

            'status'      => $this->faker->randomElement(ProductStatusEnum::values()),

            'min_stock'   => $this->faker->numberBetween(1, 10),

            'has_stock'   => $this->faker->boolean(70),  // 70% products have stock
        ];

    }

    public function configure()
    {
        return $this->afterCreating(function ($product) {
            if ($product->has_stock) {
                \App\Models\Inventory::factory()->create([
                    'product_id' => $product->id,
                ]);
            }
        });
    }

}
