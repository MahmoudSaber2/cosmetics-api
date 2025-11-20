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
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Product\StoreProductRequest;
use App\Http\Requests\V1\Product\UpdateProductRequest;
use App\Http\Resources\V1\Product\ProductCollection;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller implements HasMiddleware
{
    protected $fileUploadService;

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
     * Display a listing of products with optional search and filtering.
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
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        try{
            $data = $request->validated();
            DB::beginTransaction();
            // upload media if exists
            if ($request->hasFile('media')) {
                $mediaData = $this->fileUploadService->uploadImage($request->file('media'), 'products');
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

            if (isset($mediaData)) {
                ProductMedia::create([
                    'product_id' => $product->id,
                    'url' => $mediaData['url'],
                    'media_type' => 'image',
                ]);
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
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('inventory', 'media', 'brand', 'category');
        return ApiResponse::success(new ProductResource($product));
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try{
            $data = $request->validated();
            // upload media if exists
            if ($request->hasFile('media')) {
                // delete old image if exists
                if ($product->media) {
                    $this->fileUploadService->deleteImage($product->media->url);
                    $product->media->delete();
                }
                $mediaData = $this->fileUploadService->uploadImage($request->file('media'), 'products');
            }

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

            if (isset($mediaData)) {
                ProductMedia::create([
                    'product_id' => $product->id,
                    'url' => $mediaData['url'],
                    'media_type' => 'image',
                ]);
            }

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
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Check if product has order items
        if ($product->orderItems()->exists()) {
            return ApiResponse::error(__('messages.product_has_orders'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        // Delete image if exists
        if ($product->image_path) {
            $this->fileUploadService->deleteImage($product->image_path, $product->thumbnail_path);
        }

        // Delete inventory record
        if ($product->inventory) {
            $product->inventory->delete();
        }

        $product->delete();

        return ApiResponse::success([], __('messages.deleted'), HttpStatusCode::OK);
    }

}
