<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed']),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is approved.
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the order is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
