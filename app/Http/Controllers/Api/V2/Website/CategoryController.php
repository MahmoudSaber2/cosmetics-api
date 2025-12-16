<?php

namespace App\Http\Controllers\Api\V2\Website;

use App\Enums\StatusEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Website\CategoryCollection;
use App\Http\Resources\V1\Website\CategoryResource;
use App\Http\Resources\V1\Website\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * v1
 * landing page 5 section
 * v2
 * landaing page 5 section
 * home - contact - services without details
 * v3
 * home - contact - services with detials - about us
 */
class CategoryController extends Controller
{
    /**
     * Display all active categories
     */
    public function index()
    {
        $categories = Category::where('status', StatusEnum::ACTIVE)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(new CategoryCollection($categories));
    }

    /**
     * Display the specified category with its products
     */
    public function show($slug, Request $request)
    {
        $category = Category::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $products = Product::with(['brand', 'category', 'media', 'inventory'])
            ->where('category_id', $category->id)
            ->where('status', 'active')
            ->whereHas('inventory', function ($query) {
                $query->where('quantity', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('perPage', 12));

        return ApiResponse::success([
            'category' => new CategoryResource($category),
            'products' => new ProductCollection($products)
        ]);
    }
}
