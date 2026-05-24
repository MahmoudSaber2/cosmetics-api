<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Enums\ProductStatusEnum;
use App\Filters\Website\FilterWebsiteProduct;
use App\Filters\Website\FilterWebsiteProductPrice;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Website\ProductIndexRequest;
use App\Http\Resources\V1\Website\ProductCollection;
use App\Http\Resources\V1\Website\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Website Products",
 *     description="Product browsing and details for website visitors"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/website/products",
     *     summary="Get products list with filtering and sorting",
     *     description="Retrieve a paginated list of active products with advanced filtering and sorting options for website visitors",
     *     operationId="getWebsiteProducts",
     *     tags={"Website Products"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="filter[search]",
     *         in="query",
     *         description="Search in product name, description, brand, or category",
     *         required=false,
     *         @OA\Schema(type="string", example="هاتف")
     *     ),
     *     @OA\Parameter(
     *         name="filter[category]",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="filter[price]",
     *         in="query",
     *         description="Filter by price range (format: min,max)",
     *         required=false,
     *         @OA\Schema(type="string", example="100,500")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort products (latest, oldest, price_low, price_high, name)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"latest", "-latest", "oldest", "-oldest", "price_low", "price_high", "-price_low", "-price_high", "name", "-name"}, example="-latest")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, minimum=1, maximum=50)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="productId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="منتج تجريبي"),
     *                         @OA\Property(property="description", type="string", example="وصف المنتج"),
     *                         @OA\Property(property="slug", type="string", example="sample-product"),
     *                         @OA\Property(property="price", type="number", format="float", example=100.00),
     *                         @OA\Property(
     *                             property="brand",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="براند تجريبي")
     *                         ),
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="إلكترونيات"),
     *                             @OA\Property(property="slug", type="string", example="electronics")
     *                         ),
     *                         @OA\Property(
     *                             property="image",
     *                             type="object",
     *                             @OA\Property(property="url", type="string", example="https://example.com/image.jpg"),
     *                             @OA\Property(property="type", type="string", example="image")
     *                         ),
     *                         @OA\Property(property="hasStock", type="boolean", example=true),
     *                         @OA\Property(property="stockQuantity", type="integer", example=10),
     *                         @OA\Property(property="stockStatus", type="integer", example=2)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category",
     *                     type="array",
     *                     @OA\Items(type="string", example="التصنيف المحدد غير موجود")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(ProductIndexRequest $request)
    {
        $products = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterWebsiteProduct()),
                AllowedFilter::exact('category', 'category_id'),
                AllowedFilter::custom('price', new FilterWebsiteProductPrice()),
            ])
            ->allowedSorts([
                AllowedSort::field('latest', 'created_at'),
                AllowedSort::field('oldest', 'created_at'),
                AllowedSort::field('price_low', 'price'),
                AllowedSort::field('price_high', 'price'),
                AllowedSort::field('name', 'name'),
            ])
            ->defaultSort('-created_at')
            ->with(['brand', 'category', 'media', 'inventory'])
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereHas('inventory', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new ProductCollection($products));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/website/products/{slug}",
     *     summary="Get product details by slug",
     *     description="Retrieve detailed information about a specific product using its slug",
     *     operationId="getWebsiteProductDetails",
     *     tags={"Website Products"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(type="string", example="sample-product")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="productId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="منتج تجريبي"),
     *                 @OA\Property(property="description", type="string", example="وصف مفصل للمنتج"),
     *                 @OA\Property(property="slug", type="string", example="sample-product"),
     *                 @OA\Property(property="price", type="number", format="float", example=100.00),
     *                 @OA\Property(
     *                     property="brand",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="براند تجريبي")
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="إلكترونيات"),
     *                     @OA\Property(property="slug", type="string", example="electronics")
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="object",
     *                     @OA\Property(property="url", type="string", example="https://example.com/image.jpg"),
     *                     @OA\Property(property="type", type="string", example="image")
     *                 ),
     *                 @OA\Property(property="hasStock", type="boolean", example=true),
     *                 @OA\Property(property="stockQuantity", type="integer", example=10),
     *                 @OA\Property(property="stockStatus", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(Product $product)
    {
        $product->with(['brand', 'category', 'media', 'inventory'])
            ->where('status', ProductStatusEnum::ACTIVE)
            ->first();

        return ApiResponse::success(new ProductResource($product));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/website/products/{slug}/related",
     *     summary="Get related products",
     *     description="Retrieve products related to the specified product based on category",
     *     operationId="getRelatedProducts",
     *     tags={"Website Products"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(type="string", example="sample-product")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of related products to return",
     *         required=false,
     *         @OA\Schema(type="integer", default=4, minimum=1, maximum=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Related products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="products",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="productId", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="منتج مشابه"),
     *                         @OA\Property(property="slug", type="string", example="similar-product"),
     *                         @OA\Property(property="price", type="number", format="float", example=120.00),
     *                         @OA\Property(
     *                             property="image",
     *                             type="object",
     *                             @OA\Property(property="url", type="string", example="https://example.com/image2.jpg")
     *                         ),
     *                         @OA\Property(property="stockQuantity", type="integer", example=5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function related(Product $product, Request $request)
    {

        $relatedProducts = Product::with(['brand', 'category', 'media', 'inventory'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', ProductStatusEnum::ACTIVE)
            ->whereHas('inventory', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->limit($request->get('limit', 4))
            ->get();

        return ApiResponse::success(new ProductCollection($relatedProducts));
    }
}
