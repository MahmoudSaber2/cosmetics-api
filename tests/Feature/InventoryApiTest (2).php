<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InventoryApiTest extends TestCase
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

    public function test_admin_can_list_inventory()
    {
        // Create some test products with inventory
        $products = Product::factory()->count(3)->create();
        foreach ($products as $product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'inventory',
                    'pagination'
                ],
                'message'
            ]);
    }

    public function test_admin_can_view_inventory_item()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/inventory/{$inventory->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'product',
                    'stock_quantity',
                    'min_stock_level'
                ],
                'message'
            ]);
    }

    public function test_admin_can_update_inventory()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 50
        ]);

        $updateData = [
            'stock_quantity' => 100,
            'min_stock_level' => 15
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/inventory/{$inventory->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', [
            'id' => $inventory->id,
            'stock_quantity' => 100,
            'min_stock_level' => 15
        ]);
    }

    public function test_admin_can_update_product_stock()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 50
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/inventory/products/{$product->id}/stock", [
                'stock_quantity' => 75,
                'operation' => 'set'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 75
        ]);
    }

    public function test_admin_can_add_stock()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 50
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/inventory/products/{$product->id}/stock", [
                'stock_quantity' => 25,
                'operation' => 'add'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 75 // 50 + 25
        ]);
    }

    public function test_admin_can_subtract_stock()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 50
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/inventory/products/{$product->id}/stock", [
                'stock_quantity' => 20,
                'operation' => 'subtract'
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 30 // 50 - 20
        ]);
    }

    public function test_cannot_subtract_more_than_available_stock()
    {
        $product = Product::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 10
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/inventory/products/{$product->id}/stock", [
                'stock_quantity' => 20,
                'operation' => 'subtract'
            ]);

        $response->assertStatus(400);

        // Stock should remain unchanged
        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 10
        ]);
    }

    public function test_get_low_stock_alerts()
    {
        $product1 = Product::factory()->active()->create();
        $product2 = Product::factory()->active()->create();

        // Low stock item
        Inventory::factory()->create([
            'product_id' => $product1->id,
            'stock_quantity' => 5,
            'min_stock_level' => 10
        ]);

        // Normal stock item
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'stock_quantity' => 50,
            'min_stock_level' => 10
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory-low-stock');

        $response->assertStatus(200);

        $lowStockItems = $response->json('data');
        $this->assertCount(1, $lowStockItems);
        $this->assertEquals($product1->id, $lowStockItems[0]['product_id']);
    }

    public function test_get_out_of_stock_items()
    {
        $product1 = Product::factory()->active()->create();
        $product2 = Product::factory()->active()->create();

        // Out of stock item
        Inventory::factory()->create([
            'product_id' => $product1->id,
            'stock_quantity' => 0
        ]);

        // In stock item
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'stock_quantity' => 50
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory-out-of-stock');

        $response->assertStatus(200);

        $outOfStockItems = $response->json('data');
        $this->assertCount(1, $outOfStockItems);
        $this->assertEquals($product1->id, $outOfStockItems[0]['product_id']);
    }

    public function test_get_inventory_statistics()
    {
        $products = Product::factory()->active()->count(3)->create(['price' => 25.00]);

        // Create different stock levels
        Inventory::factory()->create([
            'product_id' => $products[0]->id,
            'stock_quantity' => 0 // Out of stock
        ]);
        Inventory::factory()->create([
            'product_id' => $products[1]->id,
            'stock_quantity' => 5,
            'min_stock_level' => 10 // Low stock
        ]);
        Inventory::factory()->create([
            'product_id' => $products[2]->id,
            'stock_quantity' => 50,
            'min_stock_level' => 10 // In stock
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_products',
                    'in_stock_products',
                    'out_of_stock_products',
                    'low_stock_products',
                    'total_stock_value',
                    'average_stock_level'
                ],
                'message'
            ]);

        $stats = $response->json('data');
        $this->assertEquals(3, $stats['total_products']);
        $this->assertEquals(1, $stats['out_of_stock_products']);
        $this->assertEquals(1, $stats['low_stock_products']);
    }

    public function test_bulk_update_stock()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product1->id,
            'stock_quantity' => 10
        ]);
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'stock_quantity' => 20
        ]);

        $updateData = [
            'updates' => [
                [
                    'product_id' => $product1->id,
                    'stock_quantity' => 50,
                    'operation' => 'set'
                ],
                [
                    'product_id' => $product2->id,
                    'stock_quantity' => 10,
                    'operation' => 'add'
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson('/api/admin/inventory-bulk-update', $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product1->id,
            'stock_quantity' => 50
        ]);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product2->id,
            'stock_quantity' => 30 // 20 + 10
        ]);
    }

    public function test_inventory_filtering_by_stock_status()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Out of stock
        Inventory::factory()->create([
            'product_id' => $product1->id,
            'stock_quantity' => 0
        ]);

        // In stock
        Inventory::factory()->create([
            'product_id' => $product2->id,
            'stock_quantity' => 50
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory?stock_status=out_of_stock');

        $response->assertStatus(200);

        $inventory = $response->json('data.inventory');
        $this->assertCount(1, $inventory);
        $this->assertEquals($product1->id, $inventory[0]['product_id']);
    }

    public function test_generate_inventory_report()
    {
        $products = Product::factory()->count(2)->create(['price' => 25.00]);

        foreach ($products as $product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/inventory-report?format=summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'generated_at',
                    'summary' => [
                        'total_products',
                        'in_stock',
                        'out_of_stock',
                        'low_stock',
                        'total_stock_value'
                    ]
                ],
                'message'
            ]);
    }
}
