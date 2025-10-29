<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnifiedApplicationIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        Storage::fake('public');
    }

    /**
     * Test admin authentication flow
     */
    public function test_admin_authentication_flow()
    {
        // Test login
        $response = $this->postJson('/api/login', [
            'email' => $this->admin->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ]
            ]
        ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Test authenticated user endpoint
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $this->admin->id,
                'email' => $this->admin->email,
            ]
        ]);

        // Test logout
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test file upload integration
     */
    public function test_file_upload_integration()
    {
        Sanctum::actingAs($this->admin);

        // Test file upload
        $file = UploadedFile::fake()->image('product.jpg', 800, 600);

        $response = $this->postJson('/api/upload', [
            'file' => $file,
            'directory' => 'products',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'original_name',
                'filename',
                'path',
                'thumbnail_path',
                'url',
                'thumbnail_url',
                'size',
                'mime_type',
            ]
        ]);

        $uploadData = $response->json('data');

        // Verify files exist in storage
        $this->assertTrue(Storage::disk('public')->exists($uploadData['path']));
        $this->assertTrue(Storage::disk('public')->exists($uploadData['thumbnail_path']));

        // Test file info endpoint
        $response = $this->getJson('/api/upload/info?path=' . urlencode($uploadData['path']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'exists' => true,
            ]
        ]);

        // Test file deletion
        $response = $this->deleteJson('/api/upload', [
            'path' => $uploadData['path'],
            'thumbnail_path' => $uploadData['thumbnail_path'],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify files are deleted
        $this->assertFalse(Storage::disk('public')->exists($uploadData['path']));
        $this->assertFalse(Storage::disk('public')->exists($uploadData['thumbnail_path']));
    }

    /**
     * Test product management with image upload
     */
    public function test_product_management_with_images()
    {
        Sanctum::actingAs($this->admin);

        $file = UploadedFile::fake()->image('foundation.jpg', 600, 600);

        // Create product with image
        $response = $this->postJson('/api/admin/products', [
            'name' => 'Test Foundation with Image',
            'description' => 'A test foundation product',
            'brand' => 'TestBrand',
            'type' => 'foundation',
            'color' => 'Beige',
            'size' => '30ml',
            'gender' => 'women',
            'price' => 45.99,
            'status' => 'active',
            'stock_quantity' => 25,
            'min_stock_level' => 5,
            'image' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'name',
                'image_url',
                'image_path',
                'thumbnail_url',
                'thumbnail_path',
                'inventory' => [
                    'stock_quantity',
                ]
            ]
        ]);

        $product = $response->json('data');
        $this->assertNotNull($product['image_url']);
        $this->assertNotNull($product['thumbnail_url']);

        // Verify image files exist
        if ($product['image_path']) {
            $this->assertTrue(Storage::disk('public')->exists($product['image_path']));
        }
        if ($product['thumbnail_path']) {
            $this->assertTrue(Storage::disk('public')->exists($product['thumbnail_path']));
        }

        // Test product update with new image
        $newFile = UploadedFile::fake()->image('new-foundation.jpg', 600, 600);

        $response = $this->putJson("/api/admin/products/{$product['id']}", [
            'name' => 'Updated Foundation',
            'description' => 'Updated description',
            'brand' => 'TestBrand',
            'type' => 'foundation',
            'color' => 'Ivory',
            'size' => '30ml',
            'gender' => 'women',
            'price' => 49.99,
            'status' => 'active',
            'image' => $newFile,
        ]);

        $response->assertStatus(200);
        $updatedProduct = $response->json('data');

        // Verify old image was replaced
        $this->assertNotEquals($product['image_url'], $updatedProduct['image_url']);
    }

    /**
     * Test CORS configuration for unified application
     */
    public function test_cors_configuration()
    {
        // Test preflight request
        $response = $this->call('OPTIONS', '/api/public/products', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:3000',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'Content-Type, Authorization',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('http://localhost:3000', $response->headers->get('Access-Control-Allow-Origin'));
    }

    /**
     * Test API rate limiting
     */
    public function test_api_rate_limiting()
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/public/products');

            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                // Should hit rate limit after 60 requests
                $response->assertStatus(429);
                break;
            }
        }
    }

    /**
     * Test public store API endpoints
     */
    public function test_public_store_api_endpoints()
    {
        // Create test products
        $products = Product::factory()->count(5)->create(['status' => 'active']);
        foreach ($products as $product) {
            Inventory::factory()->create([
                'product_id' => $product->id,
                'stock_quantity' => 20,
            ]);
        }

        // Test product listing
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
                        'brand',
                        'type',
                        'inventory',
                    ]
                ],
                'pagination',
            ]
        ]);

        // Test product detail
        $product = $products->first();
        $response = $this->getJson("/api/public/products/{$product->id}");
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
            ]
        ]);

        // Test product search
        $response = $this->getJson('/api/public/products?search=' . $product->name);
        $response->assertStatus(200);

        $searchResults = $response->json('data.data');
        $this->assertNotEmpty($searchResults);

        // Test filter options
        $response = $this->getJson('/api/public/products-filters');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'brands',
                'types',
                'colors',
                'genders',
            ]
        ]);
    }

    /**
     * Test admin dashboard data endpoints
     */
    public function test_admin_dashboard_endpoints()
    {
        Sanctum::actingAs($this->admin);

        // Create test data
        $products = Product::factory()->count(3)->create();
        $clients = Client::factory()->count(2)->create();

        foreach ($products as $product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        }

        Order::factory()->count(5)->create([
            'client_id' => $clients->random()->id,
        ]);

        // Test products endpoint
        $response = $this->getJson('/api/admin/products');
        $response->assertStatus(200);

        // Test orders endpoint
        $response = $this->getJson('/api/admin/orders');
        $response->assertStatus(200);

        // Test clients endpoint
        $response = $this->getJson('/api/admin/clients');
        $response->assertStatus(200);

        // Test inventory endpoint
        $response = $this->getJson('/api/admin/inventory');
        $response->assertStatus(200);

        // Test statistics endpoints
        $response = $this->getJson('/api/admin/orders-statistics');
        $response->assertStatus(200);

        $response = $this->getJson('/api/admin/inventory-statistics');
        $response->assertStatus(200);
    }

    /**
     * Test error handling and validation
     */
    public function test_error_handling_and_validation()
    {
        // Test invalid login
        $response = $this->postJson('/api/login', [
            'email' => 'invalid@email.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);

        // Test unauthorized access
        $response = $this->getJson('/api/admin/products');
        $response->assertStatus(401);

        // Test invalid product creation
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/admin/products', [
            'name' => '', // Invalid: empty name
            'price' => -10, // Invalid: negative price
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors',
        ]);

        // Test invalid order creation
        $response = $this->postJson('/api/public/orders', [
            'client' => [
                'email' => 'invalid-email', // Invalid email format
            ],
            'items' => [], // Invalid: empty items
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test database transactions and rollback
     */
    public function test_database_transactions()
    {
        $product = Product::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'stock_quantity' => 10,
        ]);

        $client = Client::factory()->create();

        // Test order creation with insufficient stock
        $response = $this->postJson('/api/public/orders', [
            'client' => [
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'city' => $client->city,
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 15, // More than available stock
                ],
            ]
        ]);

        $response->assertStatus(400);

        // Verify inventory wasn't changed
        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'stock_quantity' => 10,
        ]);
    }
}
