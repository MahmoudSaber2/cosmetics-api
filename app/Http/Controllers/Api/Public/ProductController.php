<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    /**
     * Display a listing of active products with filtering and search capabilities.
     */
    public function index(Request $request)
    {
        $query = Product::with('inventory')->active();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by brand
        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        // Filter by color
        if ($request->filled('color')) {
            $query->where('color', 'like', "%{$request->color}%");
        }

        // Filter by size
        if ($request->filled('size')) {
            $query->where('size', 'like', "%{$request->size}%");
        }

        // Filter by multiple brands (comma-separated)
        if ($request->filled('brands')) {
            $brands = explode(',', $request->brands);
            $query->whereIn('brand', $brands);
        }

        // Filter by multiple types (comma-separated)
        if ($request->filled('types')) {
            $types = explode(',', $request->types);
            $query->whereIn('type', $types);
        }

        // Filter by multiple colors (comma-separated)
        if ($request->filled('colors')) {
            $colors = explode(',', $request->colors);
            $query->where(function ($q) use ($colors) {
                foreach ($colors as $color) {
                    $q->orWhere('color', 'like', "%{$color}%");
                }
            });
        }

        // Filter by multiple sizes (comma-separated)
        if ($request->filled('sizes')) {
            $sizes = explode(',', $request->sizes);
            $query->where(function ($q) use ($sizes) {
                foreach ($sizes as $size) {
                    $q->orWhere('size', 'like', "%{$size}%");
                }
            });
        }

        // Price range filtering
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by stock availability
        if ($request->filled('in_stock') && $request->boolean('in_stock')) {
            $query->whereHas('inventory', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['name', 'brand', 'type', 'price', 'created_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 12), 50); // Limit to 50 items per page for public API
        $products = $query->paginate($perPage);

        // Transform products to include stock information
        $transformedProducts = $products->getCollection()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'brand' => $product->brand,
                'type' => $product->type,
                'color' => $product->color,
                'size' => $product->size,
                'gender' => $product->gender,
                'selling_price' => $product->selling_price,
                'purchase_price' => $product->purchase_price,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'discount_start_date' => $product->discount_start_date,
                'discount_end_date' => $product->discount_end_date,
                'price' => $product->getFinalPrice(), // Final price after discount
                'image_url' => $product->image_url,
                'thumbnail_url' => $product->thumbnail_url,
                'status' => $product->status,
                'inventory' => [
                    'stock_quantity' => $product->getStockQuantity(),
                    'min_stock_level' => $product->inventory ? $product->inventory->min_stock_level : 0,
                ],
                'in_stock' => $product->isInStock(),
                'is_low_stock' => $product->isLowStock(),
                'stock_quantity' => $product->getStockQuantity(),
            ];
        });

        return $this->sendResponse([
            'data' => $transformedProducts,
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ], 'Products retrieved successfully');
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        $product = Product::with('inventory')->active()->find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        $transformedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'brand' => $product->brand,
            'type' => $product->type,
            'color' => $product->color,
            'size' => $product->size,
            'gender' => $product->gender,
            'selling_price' => $product->selling_price,
            'purchase_price' => $product->purchase_price,
            'discount_type' => $product->discount_type,
            'discount_value' => $product->discount_value,
            'discount_start_date' => $product->discount_start_date,
            'discount_end_date' => $product->discount_end_date,
            'price' => $product->getFinalPrice(), // Final price after discount
            'image_url' => $product->image_url,
            'thumbnail_url' => $product->thumbnail_url,
            'status' => $product->status,
            'inventory' => [
                'stock_quantity' => $product->getStockQuantity(),
                'min_stock_level' => $product->inventory ? $product->inventory->min_stock_level : 0,
            ],
            'in_stock' => $product->isInStock(),
            'is_low_stock' => $product->isLowStock(),
            'stock_quantity' => $product->getStockQuantity(),
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];

        return $this->sendResponse($transformedProduct, 'Product retrieved successfully');
    }

    /**
     * Get available filter options for products.
     */
    public function filterOptions()
    {
        $brands = Product::active()
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        $types = Product::active()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        $colors = Product::active()
            ->select('color')
            ->whereNotNull('color')
            ->where('color', '!=', '')
            ->distinct()
            ->orderBy('color')
            ->pluck('color');

        $sizes = Product::active()
            ->select('size')
            ->whereNotNull('size')
            ->where('size', '!=', '')
            ->distinct()
            ->orderBy('size')
            ->pluck('size');

        $genders = Product::active()
            ->select('gender')
            ->distinct()
            ->orderBy('gender')
            ->pluck('gender');

        $priceRange = Product::active()
            ->selectRaw('MIN(price) as min_price, MAX(price) as max_price')
            ->first();

        return $this->sendResponse([
            'brands' => $brands,
            'types' => $types,
            'colors' => $colors,
            'sizes' => $sizes,
            'genders' => $genders,
            'price_range' => [
                'min' => $priceRange->min_price ?? 0,
                'max' => $priceRange->max_price ?? 0,
            ]
        ], 'Filter options retrieved successfully');
    }

    /**
     * Search products with advanced filtering.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $query = Product::with('inventory')->active();
        $searchTerm = $request->q;
        $limit = $request->get('limit', 10);

        $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
                ->orWhere('brand', 'like', "%{$searchTerm}%")
                ->orWhere('type', 'like', "%{$searchTerm}%")
                ->orWhere('description', 'like', "%{$searchTerm}%");
        });

        // Apply additional filters if provided
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('brand')) {
            $query->byBrand($request->brand);
        }

        $products = $query->limit($limit)->get();

        $transformedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'type' => $product->type,
                'selling_price' => $product->selling_price,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'discount_start_date' => $product->discount_start_date,
                'discount_end_date' => $product->discount_end_date,
                'price' => $product->getFinalPrice(),
                'image_url' => $product->image_url,
                'thumbnail_url' => $product->thumbnail_url,
                'status' => $product->status,
                'inventory' => [
                    'stock_quantity' => $product->getStockQuantity(),
                    'min_stock_level' => $product->inventory ? $product->inventory->min_stock_level : 0,
                ],
                'in_stock' => $product->isInStock(),
                'stock_quantity' => $product->getStockQuantity(),
            ];
        });

        return $this->sendResponse([
            'data' => $transformedProducts,
            'total' => $products->count(),
            'search_term' => $searchTerm,
        ], 'Search results retrieved successfully');
    }

    /**
     * Get featured products for homepage.
     */
    public function featured(Request $request)
    {
        $limit = min($request->get('limit', 8), 20);

        $products = Product::with('inventory')
            ->active()
            ->whereHas('inventory', function ($q) {
                $q->where('stock_quantity', '>', 0);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $transformedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'brand' => $product->brand,
                'type' => $product->type,
                'selling_price' => $product->selling_price,
                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'discount_start_date' => $product->discount_start_date,
                'discount_end_date' => $product->discount_end_date,
                'price' => $product->getFinalPrice(),
                'image_url' => $product->image_url,
                'thumbnail_url' => $product->thumbnail_url,
                'status' => $product->status,
                'inventory' => [
                    'stock_quantity' => $product->getStockQuantity(),
                    'min_stock_level' => $product->inventory ? $product->inventory->min_stock_level : 0,
                ],
                'in_stock' => $product->isInStock(),
                'stock_quantity' => $product->getStockQuantity(),
            ];
        });

        return $this->sendResponse([
            'data' => $transformedProducts,
            'total' => $products->count(),
        ], 'Featured products retrieved successfully');
    }

    /**
     * Check product availability for given quantity.
     */
    public function checkAvailability($id, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('inventory')->active()->find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        $requestedQuantity = $request->quantity;
        $availableStock = $product->getStockQuantity();
        $isAvailable = $availableStock >= $requestedQuantity;

        return $this->sendResponse([
            'available' => $isAvailable,
            'stock' => $availableStock,
            'requested' => $requestedQuantity,
            'message' => $isAvailable
                ? 'Product is available in requested quantity'
                : "Only {$availableStock} items available in stock"
        ], 'Availability checked successfully');
    }

    /**
     * Get current stock for a product.
     */
    public function getStock($id)
    {
        $product = Product::with('inventory')->active()->find($id);

        if (!$product) {
            return $this->sendError('Product not found', [], 404);
        }

        return $this->sendResponse([
            'stock' => $product->getStockQuantity(),
            'available' => $product->isInStock(),
            'is_low_stock' => $product->isLowStock(),
        ], 'Stock information retrieved successfully');
    }

    /**
     * Get applied filters from request.
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }

        if ($request->filled('brand')) {
            $filters['brand'] = $request->brand;
        }

        if ($request->filled('type')) {
            $filters['type'] = $request->type;
        }

        if ($request->filled('gender')) {
            $filters['gender'] = $request->gender;
        }

        if ($request->filled('color')) {
            $filters['color'] = $request->color;
        }

        if ($request->filled('size')) {
            $filters['size'] = $request->size;
        }

        if ($request->filled('min_price')) {
            $filters['min_price'] = $request->min_price;
        }

        if ($request->filled('max_price')) {
            $filters['max_price'] = $request->max_price;
        }

        if ($request->filled('in_stock')) {
            $filters['in_stock'] = $request->boolean('in_stock');
        }

        return $filters;
    }
}
