<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublicProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test products with inventory
        $this->createTestProducts();
    }

    private function createTestProducts()
    {
        // Active products with stock
        $this->activeProduct1 = Product::factory()->create([
            'name' => 'Red Lipstick',
            'brand' => 'MAC',
            'type' => 'lipstick',
            'color' => 'Red',
            'size' => '3g',
            'gender' => 'women',
            'price' => 25.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->activeProduct1->id,
            'stock_quantity' => 50,
            'min_stock_level' => 10
        ]);

        $this->activeProduct2 = Product::factory()->create([
            'name' => 'Foundation Cream',
            'brand' => 'Maybelline',
            'type' => 'foundation',
            'color' => 'Beige',
            'size' => '30ml',
            'gender' => 'women',
            'price' => 15.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->activeProduct2->id,
            'stock_quantity' => 0, // Out of stock
            'min_stock_level' => 5
        ]);

        $this->activeProduct3 = Product::factory()->create([
            'name' => 'Men Cologne',
            'brand' => 'Hugo Boss',
            'type' => 'perfume',
            'color' => null,
            'size' => '100ml',
            'gender' => 'men',
            'price' => 89.99,
            'status' => 'active'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->activeProduct3->id,
            'stock_quantity' => 25,
            'min_stock_level' => 5
        ]);

        // Inactive product (should not appear in public API)
        $this->inactiveProduct = Product::factory()->create([
            'name' => 'Inactive Product',
            'status' => 'inactive'
        ]);
        Inventory::factory()->create([
            'product_id' => $this->inactiveProduct->id,
            'stock_quantity' => 100
        ]);
    }

    public function test_can_list_active_products()
    {
        $response = $this->getJson('/api/public/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'brand',
                            'type',
                            'price',
                            'in_stock',
                            'stock_quantity'
                        ]
                    ],
                    'pagination',
                    'filters'
                ],
                'message'
            ]);

        $products = $response->json('data.products');
        $this->assertCount(3, $products); // Only active products

        // Verify inactive product is not included
        $productIds = collect($products)->pluck('id')->toArray();
        $this->assertNotContains($this->inactiveProduct->id, $productIds);
    }

    public function test_can_view_specific_active_product()
    {
        $response = $this->getJson("/api/public/products/{$this->activeProduct1->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'brand',
                    'type',
                    'color',
                    'size',
                    'gender',
                    'price',
                    'image_url',
                    'in_stock',
                    'stock_quantity',
                    'is_low_stock'
                ],
                'message'
            ]);

        $product = $response->json('data');
        $this->assertEquals($this->activeProduct1->id, $product['id']);
        $this->assertEquals('Red Lipstick', $product['name']);
        $this->assertTrue($product['in_stock']);
    }

    public function test_cannot_view_inactive_product()
    {
        $response = $this->getJson("/api/public/products/{$this->inactiveProduct->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found'
            ]);
    }

    public function test_can_search_products()
    {
        $response = $this->getJson('/api/public/products-search?q=lipstick');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products',
                    'total_found',
                    'search_term'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertEquals('lipstick', $data['search_term']);
        $this->assertGreaterThan(0, $data['total_found']);

        $products = $data['products'];
        $this->assertStringContainsStringIgnoringCase('lipstick', $products[0]['name']);
    }

    public function test_search_requires_minimum_query_length()
    {
        $response = $this->getJson('/api/public/products-search?q=a');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    public function test_can_filter_products_by_brand()
    {
        $response = $this->getJson('/api/public/products?brand=MAC');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertCount(1, $products);
        $this->assertEquals('MAC', $products[0]['brand']);
    }

    public function test_can_filter_products_by_type()
    {
        $response = $this->getJson('/api/public/products?type=foundation');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertCount(1, $products);
        $this->assertEquals('foundation', $products[0]['type']);
    }

    public function test_can_filter_products_by_gender()
    {
        $response = $this->getJson('/api/public/products?gender=men');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $this->assertCount(1, $products);
        $this->assertEquals('men', $products[0]['gender']);
    }

    public function test_can_filter_products_by_price_range()
    {
        $response = $this->getJson('/api/public/products?min_price=20&max_price=30');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(20, $product['price']);
            $this->assertLessThanOrEqual(30, $product['price']);
        }
    }

    public function test_can_filter_products_by_stock_availability()
    {
        $response = $this->getJson('/api/public/products?in_stock=true');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertTrue($product['in_stock']);
            $this->assertGreaterThan(0, $product['stock_quantity']);
        }
    }

    public function test_can_sort_products_by_price()
    {
        $response = $this->getJson('/api/public/products?sort_by=price&sort_order=asc');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        $prices = collect($products)->pluck('price')->toArray();
        $sortedPrices = $prices;
        sort($sortedPrices);

        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_can_get_featured_products()
    {
        $response = $this->getJson('/api/public/products-featured?limit=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'products' => [
                        '*' => [
                            'id',
                            'name',
                            'brand',
                            'type',
                            'price',
                            'image_url',
                            'in_stock'
                        ]
                    ],
                    'total_count'
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertLessThanOrEqual(2, count($data['products']));

        // Featured products should only include in-stock items
        foreach ($data['products'] as $product) {
            $this->assertTrue($product['in_stock']);
        }
    }

    public function test_can_get_filter_options()
    {
        $response = $this->getJson('/api/public/products-filters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'brands',
                    'types',
                    'colors',
                    'sizes',
                    'genders',
                    'price_range' => [
                        'min',
                        'max'
                    ]
                ],
                'message'
            ]);

        $data = $response->json('data');
        $this->assertContains('MAC', $data['brands']);
        $this->assertContains('lipstick', $data['types']);
        $this->assertContains('women', $data['genders']);
        $this->assertIsNumeric($data['price_range']['min']);
        $this->assertIsNumeric($data['price_range']['max']);
    }

    public function test_pagination_works_correctly()
    {
        // Create more products to test pagination
        Product::factory()->count(15)->create(['status' => 'active'])->each(function ($product) {
            Inventory::factory()->create(['product_id' => $product->id]);
        });

        $response = $this->getJson('/api/public/products?per_page=5');

        $response->assertStatus(200);

        $pagination = $response->json('data.pagination');
        $this->assertEquals(5, $pagination['per_page']);
        $this->assertGreaterThan(1, $pagination['last_page']);
    }

    public function test_per_page_limit_is_enforced()
    {
        $response = $this->getJson('/api/public/products?per_page=100');

        $response->assertStatus(200);

        $pagination = $response->json('data.pagination');
        $this->assertLessThanOrEqual(50, $pagination['per_page']); // Max 50 for public API
    }

    public function test_multiple_filters_work_together()
    {
        $response = $this->getJson('/api/public/products?gender=women&type=lipstick&brand=MAC');

        $response->assertStatus(200);

        $products = $response->json('data.products');
        foreach ($products as $product) {
            $this->assertEquals('women', $product['gender']);
            $this->assertEquals('lipstick', $product['type']);
            $this->assertEquals('MAC', $product['brand']);
        }
    }
}
