<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $types = ['foundation', 'lipstick', 'mascara', 'eyeshadow', 'perfume', 'moisturizer', 'cleanser'];
        $brands = ['L\'Oreal', 'Maybelline', 'MAC', 'Chanel', 'Dior', 'Revlon', 'CoverGirl'];
        $colors = ['Red', 'green', 'Brown', 'Black', 'Blue', 'Green', 'emerald', 'Nude', 'Clear'];
        $sizes = ['Small', 'Medium', 'Large', '15ml', '30ml', '50ml', '100ml'];
        $genders = ['men', 'women', 'unisex'];

        return [
            'name' => $this->faker->words(3, true) . ' ' . $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(),
            'brand' => $this->faker->randomElement($brands),
            'type' => $this->faker->randomElement($types),
            'color' => $this->faker->randomElement($colors),
            'size' => $this->faker->randomElement($sizes),
            'gender' => $this->faker->randomElement($genders),
            'price' => $this->faker->randomFloat(2, 5, 200),
            'image_url' => null,
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the product is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
