<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_admin_can_list_orders()
    {
        // Create some test orders with items
        $client = Client::factory()->create();
        $orders = Order::factory()->count(3)->create(['client_id' => $client->id]);

        foreach ($orders as $order) {
            $product = Product::factory()->create();
            Inventory::factory()->create(['product_id' => $product->id]);
            OrderItem::factory()->create([
                'order_id' => $order->id,
                'product_id' => $product->id
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'orders',
                    'pagination'
                ],
                'message'
            ]);
    }

    public function test_admin_can_create_order()
    {
        $client = Client::factory()->create();
        $product = Product::factory()->create(['price' => 25.00]);
        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 100
        ]);

        $orderData = [
            'client_id' => $client->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ],
            'notes' => 'Test order'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);

        $this->assertDatabaseHas('orders', [
            'client_id' => $client->id,
            'total_amount' => 50.00, // 2 * 25.00
            'status' => 'pending'
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 25.00
        ]);
    }

    public function test_admin_can_view_order()
    {
        $client = Client::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create(['product_id' => $product->id]);

        $order = Order::factory()->create(['client_id' => $client->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'client',
                    'order_items'
                ],
                'message'
            ]);
    }

    public function test_admin_can_approve_pending_order()
    {
        $client = Client::factory()->create();
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 100
        ]);

        $order = Order::factory()->pending()->create(['client_id' => $client->id]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'approved'
        ]);

        // Check that stock was reduced
        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 98 // 100 - 2
        ]);
    }

    public function test_admin_can_reject_pending_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->pending()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/reject", [
                'reason' => 'Out of stock'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'rejected',
            'notes' => 'Out of stock'
        ]);
    }

    public function test_admin_can_complete_approved_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->approved()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/complete");

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed'
        ]);
    }

    public function test_admin_can_delete_pending_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->pending()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id
        ]);
    }

    public function test_cannot_approve_non_pending_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->approved()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/orders/{$order->id}/approve");

        $response->assertStatus(400);
    }

    public function test_cannot_delete_approved_order()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->approved()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(400);
    }

    public function test_order_filtering_by_status()
    {
        $client = Client::factory()->create();
        Order::factory()->pending()->create(['client_id' => $client->id]);
        Order::factory()->approved()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders?status=pending');

        $response->assertStatus(200);

        $orders = $response->json('data.orders');
        $this->assertCount(1, $orders);
        $this->assertEquals('pending', $orders[0]['status']);
    }

    public function test_get_order_statistics()
    {
        $client = Client::factory()->create();
        Order::factory()->pending()->create(['client_id' => $client->id, 'total_amount' => 100]);
        Order::factory()->approved()->create(['client_id' => $client->id, 'total_amount' => 200]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_orders',
                    'pending_orders',
                    'approved_orders',
                    'total_revenue',
                    'average_order_value'
                ],
                'message'
            ]);
    }

    public function test_get_order_status_counts()
    {
        $client = Client::factory()->create();
        Order::factory()->pending()->count(2)->create(['client_id' => $client->id]);
        Order::factory()->approved()->create(['client_id' => $client->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders-status-counts');

        $response->assertStatus(200);

        $counts = $response->json('data');
        $this->assertEquals(2, $counts['pending']);
        $this->assertEquals(1, $counts['approved']);
        $this->assertEquals(0, $counts['rejected']);
        $this->assertEquals(0, $counts['completed']);
    }
}
