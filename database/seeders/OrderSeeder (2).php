<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $products = Product::with('inventory')->get();

        if ($clients->isEmpty() || $products->isEmpty()) {
            $this->command->warn('No clients or products found. Please run ClientSeeder and ProductSeeder first.');
            return;
        }

        $orderStatuses = ['pending', 'approved', 'rejected', 'completed'];
        $orderNotes = [
            'Customer requested express delivery',
            'Gift wrapping requested',
            'Delivery to office address',
            'Customer is a VIP member',
            'First-time customer',
            'Repeat customer - loyal client',
            'Special occasion order',
            'Bulk order for event',
        ];

        // Create 25 sample orders
        for ($i = 0; $i < 25; $i++) {
            $client = $clients->random();
            $status = $orderStatuses[array_rand($orderStatuses)];

            // Create order
            $order = Order::create([
                'client_id' => $client->id,
                'total_amount' => 0, // Will be calculated after adding items
                'status' => $status,
                'notes' => $orderNotes[array_rand($orderNotes)],
                'created_at' => now()->subDays(rand(0, 30)), // Orders from last 30 days
            ]);

            // Add 1-5 random products to the order
            $numItems = rand(1, 5);
            $totalAmount = 0;
            $selectedProducts = $products->random($numItems);

            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $subtotal = $quantity * $unitPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;

                // Update inventory if order is approved or completed
                if (in_array($status, ['approved', 'completed']) && $product->inventory) {
                    $newStock = max(0, $product->inventory->stock_quantity - $quantity);
                    $product->inventory->update(['stock_quantity' => $newStock]);
                }
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);
        }

        // Create some specific scenario orders for testing
        $this->createSpecificOrders($clients, $products);
    }

    /**
     * Create specific orders for testing scenarios
     */
    private function createSpecificOrders($clients, $products)
    {
        // Large order (pending approval)
        $client = $clients->first();
        $largeOrder = Order::create([
            'client_id' => $client->id,
            'total_amount' => 0,
            'status' => 'pending',
            'notes' => 'Large order for beauty salon - requires approval',
            'created_at' => now()->subDays(2),
        ]);

        $totalAmount = 0;
        $expensiveProducts = $products->where('price', '>', 50)->take(3);

        foreach ($expensiveProducts as $product) {
            $quantity = rand(5, 10);
            $unitPrice = $product->price;
            $subtotal = $quantity * $unitPrice;

            OrderItem::create([
                'order_id' => $largeOrder->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            $totalAmount += $subtotal;
        }

        $largeOrder->update(['total_amount' => $totalAmount]);

        // Recent completed order
        $client = $clients->skip(1)->first();
        $recentOrder = Order::create([
            'client_id' => $client->id,
            'total_amount' => 0,
            'status' => 'completed',
            'notes' => 'Express delivery completed successfully',
            'created_at' => now()->subHours(6),
        ]);

        $totalAmount = 0;
        $popularProducts = $products->whereIn('type', ['lipstick', 'foundation'])->take(2);

        foreach ($popularProducts as $product) {
            $quantity = rand(1, 2);
            $unitPrice = $product->price;
            $subtotal = $quantity * $unitPrice;

            OrderItem::create([
                'order_id' => $recentOrder->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            $totalAmount += $subtotal;

            // Update inventory for completed order
            if ($product->inventory) {
                $newStock = max(0, $product->inventory->stock_quantity - $quantity);
                $product->inventory->update(['stock_quantity' => $newStock]);
            }
        }

        $recentOrder->update(['total_amount' => $totalAmount]);

        // Rejected order
        $client = $clients->skip(2)->first();
        $rejectedOrder = Order::create([
            'client_id' => $client->id,
            'total_amount' => 89.99,
            'status' => 'rejected',
            'notes' => 'Order rejected due to insufficient stock',
            'created_at' => now()->subDays(5),
        ]);

        $product = $products->first();
        OrderItem::create([
            'order_id' => $rejectedOrder->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'subtotal' => $product->price,
        ]);
    }
}
