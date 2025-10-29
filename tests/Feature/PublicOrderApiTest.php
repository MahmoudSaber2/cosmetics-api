<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicOrderApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $product1;
    protected $product2;
    protected $outOfStockProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test products with inventory
        $this->product1 = Product::factory()->create([
            'name' => 'Test Lipstick',
            'price' => 25.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->product1->id,
            'stock_quantity' => 50,
            'min_stock_level' => 10
        ]);

        $this->product2 = Product::factory()->create([
            'name' => 'Test Foundation',
            'price' => 35.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->product2->id,
            'stock_quantity' => 30,
            'min_stock_level' => 5
        ]);

        $this->outOfStockProduct = Product::factory()->create([
            'name' => 'Out of Stock Product',
            'price' => 15.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->outOfStockProduct->id,
            'stock_quantity' => 0,
            'min_stock_level' => 5
        ]);
    }

    public function test_can_create_order_with_new_client()
    {
        $orderData = [
            'client' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St',
                'city' => 'New York'
            ],
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 1
                ]
            ],
            'notes' => 'Please handle with care'
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order' => [
                        'id',
                        'client',
                        'items',
                        'total_amount',
                        'status',
                        'notes',
                        'created_at'
                    ]
                ],
                'message'
            ]);

        $order = $response->json('data.order');
        $this->assertEquals('pending', $order['status']);
        $this->assertEquals('Please handle with care', $order['notes']);
        $this->assertEquals(87.97, $order['total_amount']); // (25.99 * 2) + (35.99 * 1)

        // Verify client was created
        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'total_amount' => 87.97,
            'status' => 'pending'
        ]);

        // Verify order items were created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 25.99
        ]);

        // Verify inventory was updated
        $this->assertDatabaseHas('inventory', [
            'product_id' => $this->product1->id,
            'stock_quantity' => 48 // 50 - 2
        ]);
        $this->assertDatabaseHas('inventory', [
            'product_id' => $this->product2->id,
            'stock_quantity' => 29 // 30 - 1
        ]);
    }

    public function test_can_create_order_with_existing_client()
    {
        $existingClient = Client::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com'
        ]);

        $orderData = [
            'client' => [
                'name' => 'Jane Smith', // Updated name
                'email' => 'jane@example.com', // Same email
                'phone' => '+9876543210'
            ],
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(201);

        // Verify client was updated, not duplicated
        $this->assertDatabaseHas('clients', [
            'id' => $existingClient->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+9876543210'
        ]);

        $this->assertEquals(1, Client::where('email', 'jane@example.com')->count());
    }

    public function test_cannot_order_out_of_stock_product()
    {
        $orderData = [
            'client' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'items' => [
                [
                    'product_id' => $this->outOfStockProduct->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        $errors = $response->json('errors.items');
        $this->assertStringContainsString('Insufficient stock', $errors[0]);
    }

    public function test_cannot_order_more_than_available_stock()
    {
        $orderData = [
            'client' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 100 // More than available (50)
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);

        $errors = $response->json('errors.items');
        $this->assertStringContainsString('Insufficient stock', $errors[0]);
        $this->assertStringContainsString('Available: 50', $errors[0]);
        $this->assertStringContainsString('Requested: 100', $errors[0]);
    }

    public function test_cannot_order_inactive_product()
    {
        $inactiveProduct = Product::factory()->create(['status' => 'inactive']);
        Inventory::factory()->create([
            'product_id' => $inactiveProduct->id,
            'stock_quantity' => 100
        ]);

        $orderData = [
            'client' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'items' => [
                [
                    'product_id' => $inactiveProduct->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_order_creation_requires_client_and_items()
    {
        $response = $this->postJson('/api/public/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client', 'items']);
    }

    public function test_order_creation_validates_client_data()
    {
        $orderData = [
            'client' => [
                'name' => '', // Empty name
                'email' => 'invalid-email' // Invalid email
            ],
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client.name', 'client.email']);
    }

    public function test_order_creation_validates_items_data()
    {
        $orderData = [
            'client' => [
                'name' => 'John Doe',
                'email' => 'john@example.com'
            ],
            'items' => [
                [
                    'product_id' => 999999, // Non-existent product
                    'quantity' => 0 // Invalid quantity
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id', 'items.0.quantity']);
    }

    public function test_can_view_order_details()
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'total_amount' => 50.00,
            'status' => 'pending'
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product1->id,
            'quantity' => 2,
            'unit_price' => 25.00,
            'subtotal' => 50.00
        ]);

        $response = $this->getJson("/api/public/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order' => [
                        'id',
                        'client',
                        'items',
                        'total_amount',
                        'status',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'message'
            ]);

        $orderData = $response->json('data.order');
        $this->assertEquals($order->id, $orderData['id']);
        $this->assertEquals('pending', $orderData['status']);
        $this->assertEquals(50.00, $orderData['total_amount']);
    }

    public function test_cannot_view_nonexistent_order()
    {
        $response = $this->getJson('/api/public/orders/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Order not found'
            ]);
    }

    public function test_can_track_orders_by_email()
    {
        $client = Client::factory()->create(['email' => 'test@example.com']);

        $order1 = Order::factory()->create([
            'client_id' => $client->id,
            'total_amount' => 25.99,
            'status' => 'pending'
        ]);

        $order2 = Order::factory()->create([
            'client_id' => $client->id,
            'total_amount' => 35.99,
            'status' => 'approved'
        ]);

        OrderItem::factory()->create(['order_id' => $order1->id]);
        OrderItem::factory()->create(['order_id' => $order2->id]);

        $response = $this->getJson('/api/public/orders-track?email=test@example.com');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'client',
                    'orders',
                    'total_orders'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertEquals(2, $data['total_orders']);
        $this->assertEquals('test@example.com', $data['client']['email']);
    }

    public function test_cannot_track_orders_for_nonexistent_email()
    {
        $response = $this->getJson('/api/public/orders-track?email=nonexistent@example.com');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No orders found'
            ]);
    }

    public function test_can_validate_cart_items()
    {
        $cartData = [
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 1
                ],
                [
                    'product_id' => $this->outOfStockProduct->id,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/public/orders-validate-cart', $cartData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_valid',
                    'items',
                    'total_amount',
                    'summary'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertFalse($data['is_valid']); // Should be false due to out of stock item
        $this->assertEquals(3, $data['summary']['total_items']);
        $this->assertEquals(2, $data['summary']['valid_items']);
        $this->assertEquals(1, $data['summary']['invalid_items']);

        // Check individual item validation
        $items = $data['items'];
        $this->assertTrue($items[0]['is_valid']); // product1
        $this->assertTrue($items[1]['is_valid']); // product2
        $this->assertFalse($items[2]['is_valid']); // out of stock product
    }

    public function test_cart_validation_requires_items()
    {
        $response = $this->postJson('/api/public/orders-validate-cart', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }
}
