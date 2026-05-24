<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EndToEndOrderWorkflowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $products;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        // Create test products with inventory
        $this->products = collect([
            Product::factory()->create([
                'name' => 'Test Foundation',
                'price' => 45.99,
                'status' => 'active',
            ]),
            Product::factory()->create([
                'name' => 'Test Lipstick',
                'price' => 24.99,
                'status' => 'active',
            ]),
        ]);

        // Create inventory for products
        foreach ($this->products as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'stock_quantity' => 50,
                'min_stock_level' => 10,
            ]);
        }
    }

    /**
     * Test complete order workflow from browsing to admin approval
     */
    public function test_complete_order_workflow()
    {
        // Step 1: Browse products (public API)
        $response = $this->getJson('/api/public/products');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'status',
                        'inventory' => [
                            'stock_quantity'
                        ]
                    ]
                ]
            ]
        ]);

        $availableProducts = $response->json('data.data');
        $this->assertNotEmpty($availableProducts);

        // Step 2: Get product details
        $selectedProduct = $this->products->first();
        $response = $this->getJson("/api/public/products/{$selectedProduct->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $selectedProduct->id,
                'name' => $selectedProduct->name,
            ]
        ]);

        // Step 3: Create client and place order
        $clientData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
        ];

        $orderData = [
            'client' => $clientData,
            'items' => [
                [
                    'product_id' => $this->products[0]->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $this->products[1]->id,
                    'quantity' => 1,
                ],
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'client_id',
                'total_amount',
                'status',
                'items' => [
                    '*' => [
                        'product_id',
                        'quantity',
                        'unit_price',
                        'subtotal',
                    ]
                ]
            ]
        ]);

        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'pending',
        ]);

        // Verify client was created
        $clientId = $response->json('data.client_id');
        $this->assertDatabaseHas('clients', [
            'id' => $clientId,
            'email' => $clientData['email'],
        ]);

        // Step 4: Admin login and view pending orders
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/orders?status=pending');
        $response->assertStatus(200);

        $pendingOrders = $response->json('data.data');
        $this->assertNotEmpty($pendingOrders);

        $foundOrder = collect($pendingOrders)->firstWhere('id', $orderId);
        $this->assertNotNull($foundOrder);

        // Step 5: Admin views order details
        $response = $this->getJson("/api/admin/orders/{$orderId}");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $orderId,
                'status' => 'pending',
            ]
        ]);

        // Step 6: Admin approves the order
        $response = $this->patchJson("/api/admin/orders/{$orderId}/approve");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'approved',
            ]
        ]);

        // Verify order status updated
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'approved',
        ]);

        // Step 7: Verify inventory was updated
        foreach ($orderData['items'] as $item) {
            $product = Product::find($item['product_id']);
            $expectedStock = 50 - $item['quantity']; // Initial stock was 50

            $this->assertDatabaseHas('inventory', [
                'product_id' => $product->id,
                'stock_quantity' => $expectedStock,
            ]);
        }

        // Step 8: Complete the order
        $response = $this->patchJson("/api/admin/orders/{$orderId}/complete");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'completed',
            ]
        ]);

        // Step 9: Verify final order status
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'completed',
        ]);
    }

    /**
     * Test order rejection workflow
     */
    public function test_order_rejection_workflow()
    {
        // Create client and order
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => 'pending',
        ]);

        // Admin login
        Sanctum::actingAs($this->admin);

        // Reject the order
        $response = $this->patchJson("/api/admin/orders/{$order->id}/reject");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'rejected',
            ]
        ]);

        // Verify order status
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'rejected',
        ]);
    }

    /**
     * Test inventory management integration
     */
    public function test_inventory_management_integration()
    {
        Sanctum::actingAs($this->admin);

        $product = $this->products->first();
        $originalStock = $product->inventory->stock_quantity;

        // Update inventory
        $newStock = 25;
        $response = $this->patchJson("/api/admin/inventory/{$product->inventory->id}", [
            'quantity' => $newStock,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'stock_quantity' => $newStock,
            ]
        ]);

        // Verify inventory updated
        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => $newStock,
        ]);

        // Test low stock detection
        $response = $this->getJson('/api/admin/inventory-low-stock');
        $response->assertStatus(200);

        // Update to very low stock
        $lowStock = 5;
        $this->patchJson("/api/admin/inventory/{$product->inventory->id}", [
            'quantity' => $lowStock,
        ]);

        $response = $this->getJson('/api/admin/inventory-low-stock');
        $response->assertStatus(200);

        $lowStockItems = $response->json('data');
        $foundLowStock = collect($lowStockItems)->firstWhere('product_id', $product->id);
        $this->assertNotNull($foundLowStock);
    }

    /**
     * Test out of stock prevention
     */
    public function test_out_of_stock_prevention()
    {
        $product = $this->products->first();

        // Set product to out of stock
        $product->inventory->update(['stock_quantity' => 0]);

        // Try to place order with out of stock product
        $clientData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
        ];

        $orderData = [
            'client' => $clientData,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ]
        ];

        $response = $this->postJson('/api/public/orders', $orderData);
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test product filtering and search
     */
    public function test_product_filtering_and_search()
    {
        // Test search
        $response = $this->getJson('/api/public/products?search=Foundation');
        $response->assertStatus(200);

        $products = $response->json('data.data');
        $foundProduct = collect($products)->firstWhere('name', 'Test Foundation');
        $this->assertNotNull($foundProduct);

        // Test filter by gender
        $response = $this->getJson('/api/public/products?gender=women');
        $response->assertStatus(200);

        // Test filter by type
        $response = $this->getJson('/api/public/products?type=foundation');
        $response->assertStatus(200);

        // Test price range filter
        $response = $this->getJson('/api/public/products?min_price=20&max_price=50');
        $response->assertStatus(200);
    }

    /**
     * Test admin dashboard statistics
     */
    public function test_admin_dashboard_statistics()
    {
        Sanctum::actingAs($this->admin);

        // Create some test data
        $client = Client::factory()->create();
        Order::factory()->create(['status' => 'pending', 'client_id' => $client->id]);
        Order::factory()->create(['status' => 'approved', 'client_id' => $client->id]);
        Order::factory()->create(['status' => 'completed', 'client_id' => $client->id]);

        // Test order statistics
        $response = $this->getJson('/api/admin/orders-statistics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_orders',
                'pending_orders',
                'approved_orders',
                'completed_orders',
                'total_revenue',
            ]
        ]);

        // Test inventory statistics
        $response = $this->getJson('/api/admin/inventory-statistics');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_products',
                'low_stock_count',
                'out_of_stock_count',
                'total_stock_value',
            ]
        ]);
    }

    /**
     * Test client order history
     */
    public function test_client_order_history()
    {
        $client = Client::factory()->create();
        $orders = Order::factory()->count(3)->create(['client_id' => $client->id]);

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/admin/clients/{$client->id}/orders");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'total_amount',
                    'status',
                    'created_at',
                ]
            ]
        ]);

        $clientOrders = $response->json('data');
        $this->assertCount(3, $clientOrders);
    }
}
