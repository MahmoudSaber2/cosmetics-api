<?php

namespace Database\Seeders;

use App\Enums\OrderPaidEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class V2OrderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds for v2 orders with payment integration.
     */
    public function run(): void
    {
        $this->command->info('Creating enhanced order records for v2...');

        $clients = Client::all();
        $products = Product::all();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientSeeder first.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        // Create orders with different payment scenarios
        $orderScenarios = [
            [
                'status' => OrderStatusEnum::APPROVED,
                'payment_status' => OrderPaidEnum::PAID,
                'description' => 'Completed order with successful payment',
            ],
            [
                'status' => OrderStatusEnum::PENDING,
                'payment_status' => OrderPaidEnum::UNPAID,
                'description' => 'Pending order awaiting payment',
            ],
            [
                'status' => OrderStatusEnum::COMPLETED,
                'payment_status' => OrderPaidEnum::PAID,
                'description' => 'Completed order with confirmed payment',
            ],
            [
                'status' => OrderStatusEnum::REJECTED,
                'payment_status' => OrderPaidEnum::UNPAID,
                'description' => 'Rejected order with failed payment',
            ],
            [
                'status' => OrderStatusEnum::APPROVED,
                'payment_status' => OrderPaidEnum::PAID,
                'description' => 'Another approved order',
            ],
        ];

        foreach ($orderScenarios as $index => $scenario) {
            $client = $clients->random();
            $orderProducts = $products->random(rand(1, 4));

            $subtotal = 0;
            $orderItems = [];

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $cost = $price * 0.6; // Assume 60% cost ratio
                $totalPrice = $price * $quantity;
                $totalCost = $cost * $quantity;
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'cost' => $cost,
                    'total_price' => $totalPrice,
                    'total_cost' => $totalCost,
                ];
            }

            // Calculate totals
            $discountAmount = $subtotal > 100 ? $subtotal * 0.1 : 0; // 10% discount for orders > 100
            $totalAfterDiscount = $subtotal - $discountAmount;
            $totalCost = $subtotal * 0.6; // Assume 60% cost ratio

            // Create order
            $order = Order::create([
                'number' => 'ORD-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                'client_id' => $client->id,
                'status' => $scenario['status'],
                'payment_status' => $scenario['payment_status'],
                'total_amount' => $subtotal,
                'total_cost' => $totalCost,
                'discount' => $discountAmount,
                'total_after_discount' => $totalAfterDiscount,
                'discount_type' => $discountAmount > 0 ? 1 : 0, // 1 for percentage, 0 for no discount
                'note' => $scenario['description'],
                'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'cost' => $itemData['cost'],
                    'total_price' => $itemData['total_price'],
                    'total_cost' => $itemData['total_cost'],
                ]);
            }

            $this->command->line("Created order #{$order->number} - {$scenario['description']}");
        }

        $this->command->info('✅ Created ' . count($orderScenarios) . ' enhanced order records for v2');
        $this->command->line('');
        $this->command->line('Order Status Summary:');
        foreach (OrderStatusEnum::cases() as $status) {
            $count = Order::where('status', $status)->count();
            if ($count > 0) {
                $statusName = match($status) {
                    OrderStatusEnum::PENDING => 'معلق',
                    OrderStatusEnum::APPROVED => 'موافق عليه',
                    OrderStatusEnum::REJECTED => 'مرفوض',
                    OrderStatusEnum::COMPLETED => 'مكتمل',
                };
                $this->command->line("- {$statusName}: {$count} orders");
            }
        }

        $this->command->line('');
        $this->command->line('Payment Status Summary:');
        foreach (OrderPaidEnum::cases() as $status) {
            $count = Order::where('payment_status', $status)->count();
            if ($count > 0) {
                $statusName = match($status) {
                    OrderPaidEnum::UNPAID => 'غير مدفوع',
                    OrderPaidEnum::PAID => 'مدفوع',
                };
                $this->command->line("- {$statusName}: {$count} orders");
            }
        }
    }
}
