<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductApiTest extends TestCase
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

        // Fake the storage for image uploads
        Storage::fake('public');
    }

    public function test_admin_can_list_products()
    {
        // Create some test products with inventory
        $products = Product::factory()->count(3)->create();
        foreach ($products as $product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products',
                    'pagination'
                ],
                'message'
            ]);
    }

    public function test_admin_can_create_product()
    {
        $image = UploadedFile::fake()->image('product.jpg');

        $productData = [
            'name' => 'Test Foundation',
            'description' => 'A great foundation for all skin types',
            'brand' => 'Test Brand',
            'type' => 'foundation',
            'color' => 'Beige',
            'size' => '30ml',
            'gender' => 'women',
            'price' => 29.99,
            'status' => 'active',
            'stock_quantity' => 100,
            'min_stock_level' => 10,
            'image' => $image
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Foundation',
            'brand' => 'Test Brand'
        ]);

        $this->assertDatabaseHas('inventory', [
            'stock_quantity' => 100,
            'min_stock_level' => 10
        ]);

        // Check if image was stored
        Storage::disk('public')->assertExists('products/' . $image->hashName());
    }

    public function test_admin_can_view_product()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'brand',
                    'inventory'
                ],
                'message'
            ]);
    }

    public function test_admin_can_update_product()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create(['product_id' => $product->id]);

        $updateData = [
            'name' => 'Updated Product Name',
            'brand' => $product->brand,
            'type' => $product->type,
            'gender' => $product->gender,
            'price' => 39.99,
            'status' => 'active',
            'stock_quantity' => 50
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 39.99
        ]);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 50
        ]);
    }

    public function test_admin_can_delete_product_without_orders()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create(['product_id' => $product->id]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id
        ]);
    }

    public function test_product_search_functionality()
    {
        Product::factory()->create(['name' => 'Red Lipstick', 'brand' => 'MAC']);
        Product::factory()->create(['name' => 'Blue Eyeshadow', 'brand' => 'Maybelline']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products?search=Red');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertCount(1, $products);
        $this->assertStringContainsString('Red', $products[0]['name']);
    }

    public function test_product_filtering_by_brand()
    {
        Product::factory()->create(['brand' => 'MAC']);
        Product::factory()->create(['brand' => 'Maybelline']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products?brand=MAC');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertCount(1, $products);
        $this->assertEquals('MAC', $products[0]['brand']);
    }

    public function test_get_product_categories()
    {
        Product::factory()->create(['type' => 'foundation']);
        Product::factory()->create(['type' => 'lipstick']);
        Product::factory()->create(['type' => 'foundation']); // Duplicate

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products-categories');

        $response->assertStatus(200);

        $categories = $response->json('data');
        $this->assertCount(2, $categories); // Should be unique
        $this->assertContains('foundation', $categories);
        $this->assertContains('lipstick', $categories);
    }

    public function test_get_product_brands()
    {
        Product::factory()->create(['brand' => 'MAC']);
        Product::factory()->create(['brand' => 'Maybelline']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products-brands');

        $response->assertStatus(200);

        $brands = $response->json('data');
        $this->assertContains('MAC', $brands);
        $this->assertContains('Maybelline', $brands);
    }

    public function test_bulk_update_product_status()
    {
        $products = Product::factory()->count(3)->create(['status' => 'active']);
        $productIds = $products->pluck('id')->toArray();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson('/api/admin/products-bulk-status', [
                'product_ids' => $productIds,
                'status' => 'inactive'
            ]);

        $response->assertStatus(200);

        foreach ($products as $product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'status' => 'inactive'
            ]);
        }
    }
}
