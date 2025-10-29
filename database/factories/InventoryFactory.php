<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inventory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'stock_quantity' => $this->faker->numberBetween(0, 500),
            'min_stock_level' => $this->faker->numberBetween(5, 20),
        ];
    }

    /**
     * Indicate that the inventory is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }

    /**
     * Indicate that the inventory is low in stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn(array $attributes) => [
            'stock_quantity' => 3,
            'min_stock_level' => 10,
        ]);
    }
}
