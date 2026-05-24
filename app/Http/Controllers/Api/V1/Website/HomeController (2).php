<?php

namespace App\Http\Controllers\Api\V1\Website;

use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\CategoryCollection;
use App\Http\Resources\V1\Website\CategoryResource;
use App\Http\Resources\V1\Website\ProductCollection;
use App\Http\Resources\V1\Website\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Website Home",
 *     description="Homepage data for website visitors"
 * )
 */
class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/website/home",
     *     summary="Get homepage data",
     *     description="Retrieve homepage data including active categories and featured products for website visitors",
     *     operationId="getHomepageData",
     *     tags={"Website Home"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Homepage data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="categoryId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="إلكترونيات"),
     *                         @OA\Property(property="slug", type="string", example="electronics"),
     *                         @OA\Property(property="description", type="string", example="جميع المنتجات الإلكترونية")
     *                     )
     *                 ),
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
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Get active categories
        $categories = Category::where('status', StatusEnum::ACTIVE)
            ->orderBy('name')
            ->get();

        // Get featured/latest products (limit to 8 for homepage)
        $products = Product::with(['brand', 'category', 'media', 'inventory'])
            ->where('status', StatusEnum::ACTIVE)
            ->where(function ($query) {
                $query->where('has_stock', 0) // ignore inventory
                    ->orWhere(function ($q) {
                        $q->where('has_stock', 1)
                            ->whereHas('inventory', function ($inv) {
                                $inv->where('quantity', '>', 0);
                            });
                    });
            })
            ->latest()
            ->limit(9)
            ->get();

        return ApiResponse::success([
            'categories' => CategoryResource::collection($categories),
            'products' => ProductResource::collection($products)
        ]);
    }
}
