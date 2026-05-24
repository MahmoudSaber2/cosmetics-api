<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ProductStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Product\FilterProduct;
use App\Filters\Product\FilterProductPrice;
use App\Filters\Product\FilterProductStockStatus;
use App\Helpers\ApiResponse;
use App\Http\Resources\V1\Product\ProductResource;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Product\StoreProductRequest;
use App\Http\Requests\V1\Product\UpdateProductRequest;
use App\Http\Resources\V1\Product\ProductCollection;
use App\Models\ProductMedia;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class ProductController extends Controller implements HasMiddleware
{
    public function __construct(protected FileUploadService $fileUploadService)
    {

    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('permission:all_products', only:['index']),
            new Middleware('permission:create_product', only:['store']),
            new Middleware('permission:edit_product', only:['show']),
            new Middleware('permission:update_product', only:['update']),
            new Middleware('permission:delete_product', only:['destroy']),
        ];
    }
    /**
     * @OA\Get(
     *     path="/api/v1/admin/products",
     *     operationId="getAllProducts",
     *     summary="Get all products",
     *     description="Retrieve a paginated list of products with optional filters for search, status, brand, category, stock status, and price range.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="filter[search]",
     *         in="query",
     *         description="Search by product name or description",
     *         required=false,
     *         @OA\Schema(type="string", example="iPhone")
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter products by status 0 => inactive or 1 => active",
     *         required=false,
     *         @OA\Schema(type="string", enum={0, 1}, example="1")
     *     ),
     *     @OA\Parameter(
     *         name="filter[brand]",
     *         in="query",
     *         description="Filter products by brand ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="filter[category]",
     *         in="query",
     *         description="Filter products by category ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="filter[stockStatus]",
     *         in="query",
     *         description="Filter products by stock status (0=out of stock, 1=low stock, 2=in stock, 3=no inventory)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3}, example=2)
     *     ),
     *     @OA\Parameter(
     *         name="filter[price]",
     *         in="query",
     *         description="Filter products by price range (format: min,max)",
     *         required=false,
     *         @OA\Schema(type="string", example="100,500")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort products by field",
     *         required=false,
     *         @OA\Schema(type="string", enum={"created_at", "-created_at", "price", "-price", "cost", "-cost"}, example="-created_at")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="productId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="iPhone 14"),
     *                         @OA\Property(property="price", type="number", example=999.99),
     *                         @OA\Property(property="cost", type="number", example=700.00),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="hasStock", type="boolean", example=0),
     *                         @OA\Property(property="minStock", type="integer", example=10),
     *                         @OA\Property(
     *                             property="brand",
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="Apple")
     *                         ),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="Electronics")
     *                         ),
     *                         @OA\Property(
     *                             property="media",
     *                             type="object",
     *                             @OA\Property(property="url", type="string", example="products/iphone14.jpg"),
     *                             @OA\Property(property="mediaType", type="string", example="image")
     *                         ),
     *                         @OA\Property(
     *                             property="inventory",
     *                             type="object",
     *                             @OA\Property(property="quantity", type="integer", example=50),
     *                             @OA\Property(property="stockStatus", type="integer", example=2)
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=200),
     *                     @OA\Property(property="count", type="integer", example=15),
     *                     @OA\Property(property="perPage", type="integer", example=15),
     *                     @OA\Property(property="currentPage", type="integer", example=1),
     *                     @OA\Property(property="totalPages", type="integer", example=14)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to view products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database or system error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterProduct()),
                AllowedFilter::exact('status', 'status'),
                AllowedFilter::exact('brand', 'brand_id'),
                AllowedFilter::exact('category', 'category_id'),
                AllowedFilter::custom('stockStatus', new FilterProductStockStatus()),
                AllowedFilter::custom('price', new FilterProductPrice()),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at', 'price', 'cost'])
            ->with(['brand', 'category', 'media', 'inventory'])
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new ProductCollection($products));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/products",
     *     operationId="storeProduct",
     *     summary="Create a new product",
     *     description="Creates a new product with optional media upload, brand, category, and inventory management.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "slug", "price", "status", "hasStock"},
     *                 @OA\Property(property="name", type="string", example="iPhone 14", description="Product name (must be unique)"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Latest iPhone model with advanced features"),
     *                 @OA\Property(property="slug", type="string", example="iphone-14", description="Product slug (must be unique)"),
     *                 @OA\Property(property="price", type="number", example=999.99, description="Product selling price"),
     *                 @OA\Property(property="cost", type="number", nullable=true, example=700.00, description="Product cost price"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive", "draft"}, example="active", description="Product status"),
     *                 @OA\Property(property="brandId", type="integer", nullable=true, example=1, description="Brand ID (must exist in brands table)"),
     *                 @OA\Property(property="categoryId", type="integer", nullable=true, example=1, description="Category ID (must exist in categories table)"),
     *                 @OA\Property(property="hasStock", type="boolean", example=0, description="Whether product has stock management"),
     *                 @OA\Property(property="minStock", type="integer", nullable=true, example=10, description="Minimum stock level"),
     *                 @OA\Property(property="media", type="string", format="binary", nullable=true, description="Product image (jpg,jpeg,png,webp, max 5MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product created successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
     *                     type="array",
     *                     @OA\Items(type="string", example="The slug has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to create products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database transaction failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while creating the product."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        try{
            $data = $request->validated();
            DB::beginTransaction();
            // upload media if exists
            $uploadedMedia = [];
            if ($request->hasFile('media')) {
                $files = $request->file('media');
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    $mediaData = $this->fileUploadService->uploadImage($file, 'products');
                    $uploadedMedia[] = $mediaData;
                }
            }

            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'slug' => $data['slug'],
                'status' => ProductStatusEnum::from($data['status'])->value,
                'brand_id' => $data['brandId'] ?? null,
                'category_id' => $data['categoryId'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'price' => $data['price']??0,
                'min_stock' => $data['minStock'] ?? 0,
                'has_stock' => $data['hasStock'],
            ]);

            if (!empty($uploadedMedia)) {
                foreach ($uploadedMedia as $index => $mediaData) {
                    ProductMedia::create([
                        'product_id' => $product->id,
                        'url' => $mediaData['path'],
                        'media_type' => 'image',
                        'is_main' => $index === 0, // First image is main
                    ]);
                }
            }

            if(isset($data['minStock']) || isset($data['hasStock'])){
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $data['hasStock'] ? $data['minStock'] ?? 0 : 0,
                ]);

            }
            DB::commit();
            return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED );


        }catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error(__('messages.error'), ['error' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/products/{id}",
     *     operationId="showProduct",
     *     summary="Get product details",
     *     description="Retrieve detailed information about a specific product by ID including inventory, media, brand, and category.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="productId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="iPhone 14"),
     *                 @OA\Property(property="description", type="string", example="Latest iPhone model"),
     *                 @OA\Property(property="slug", type="string", example="iphone-14"),
     *                 @OA\Property(property="price", type="number", example=999.99),
     *                 @OA\Property(property="cost", type="number", example=700.00),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="hasStock", type="boolean", example=0),
     *                 @OA\Property(property="minStock", type="integer", example=10),
     *                 @OA\Property(
     *                     property="brand",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Apple")
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Electronics")
     *                 ),
     *                 @OA\Property(
     *                     property="media",
     *                     type="object",
     *                     @OA\Property(property="url", type="string", example="products/iphone14.jpg"),
     *                     @OA\Property(property="mediaType", type="string", example="image")
     *                 ),
     *                 @OA\Property(
     *                     property="inventory",
     *                     type="object",
     *                     @OA\Property(property="quantity", type="integer", example=50),
     *                     @OA\Property(property="stockStatus", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to view product details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        $product->load('inventory', 'media', 'brand', 'category');
        return ApiResponse::success(new ProductResource($product));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/products/{id}",
     *     operationId="updateProduct",
     *     summary="Update product information",
     *     description="Updates an existing product's information including media, brand, category, and inventory management.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "slug", "price", "status", "hasStock"},
     *                 @OA\Property(property="name", type="string", example="iPhone 14 Pro", description="Product name (must be unique)"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Updated iPhone model with pro features"),
     *                 @OA\Property(property="slug", type="string", example="iphone-14-pro", description="Product slug (must be unique)"),
     *                 @OA\Property(property="price", type="number", example=1199.99, description="Product selling price"),
     *                 @OA\Property(property="cost", type="number", nullable=true, example=800.00, description="Product cost price"),
     *                 @OA\Property(property="status", type="string", enum={"active", "inactive", "draft"}, example="active", description="Product status"),
     *                 @OA\Property(property="brandId", type="integer", nullable=true, example=1, description="Brand ID (must exist in brands table)"),
     *                 @OA\Property(property="categoryId", type="integer", nullable=true, example=1, description="Category ID (must exist in categories table)"),
     *                 @OA\Property(property="hasStock", type="boolean", example=true, description="Whether product has stock management"),
     *                 @OA\Property(property="minStock", type="integer", nullable=true, example=15, description="Minimum stock level"),
     *                 @OA\Property(property="media", type="string", format="binary", nullable=true, description="Product image (jpg,jpeg,png,webp, max 5MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product updated successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
     *                     type="array",
     *                     @OA\Items(type="string", example="The slug has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to update products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database transaction failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the product."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try{
            $data = $request->validated();
            // Note: Media management is now handled separately through ProductMediaController

            $product->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'slug' => $data['slug'],
                'status' => ProductStatusEnum::from($data['status'])->value,
                'brand_id' => $data['brandId'] ?? null,
                'category_id' => $data['categoryId'] ?? null,
                'cost' => $data['cost'] ?? 0,
                'price' => $data['price']??0,
                'min_stock' => $data['minStock'] ?? 0,
                'has_stock' => $data['hasStock'],
            ]);



            // Update or create inventory
            if(isset($data['minStock']) || isset($data['hasStock'])){
                $inventoryData = [
                    'quantity' => $data['hasStock'] ? $data['minStock'] ?? 0 : 0,
                ];
                Inventory::updateOrCreate(
                    ['product_id' => $product->id],
                    $inventoryData
                );
            }

            DB::commit();
            return ApiResponse::success([], __('messages.updated'), HttpStatusCode::OK);
        }catch(\Exception $e){
            DB::rollBack();
            return ApiResponse::error(__('messages.error'), ['error' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/products/{id}",
     *     operationId="deleteProduct",
     *     summary="Delete a product",
     *     description="Permanently delete a product from the system. Cannot delete products with existing orders.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete product with existing orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete product with existing orders."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to delete products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        // Check if product has active order items (not soft deleted)
        if ($product->orderItems()->whereNull('deleted_at')->exists()) {
            return ApiResponse::error(__('messages.product_has_orders'), [], HttpStatusCode::BAD_REQUEST);
        }

        // Delete image if exists
        if ($product->product_media->getRawOriginal('url')) {
            $this->fileUploadService->deleteImage($product->product_media->getRawOriginal('url'));
        }

        // Delete inventory record
        if ($product->inventory) {
            $product->inventory->delete();
        }

        $product->delete();

        return ApiResponse::success([], __('messages.deleted'), HttpStatusCode::OK);
    }

}
