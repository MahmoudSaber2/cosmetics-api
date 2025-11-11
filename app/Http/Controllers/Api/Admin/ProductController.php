<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\Inventory;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseApiController
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display a listing of products with optional search and filtering.
     */
    public function index(Request $request)
    {
        $query = Product::with('inventory');

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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->whereHas('inventory', function ($q) {
                        $q->where('stock_quantity', '>', 0);
                    });
                    break;
                case 'out_of_stock':
                    $query->whereHas('inventory', function ($q) {
                        $q->where('stock_quantity', '<=', 0);
                    });
                    break;
                case 'low_stock':
                    $query->whereHas('inventory', function ($q) {
                        $q->whereRaw('stock_quantity <= min_stock_level AND stock_quantity > 0');
                    });
                    break;
            }
        }

        // Price range filtering
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'brand', 'type', 'selling_price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $products = $query->paginate($perPage);

        return $this->sendResponse([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
            'from' => $products->firstItem(),
            'to' => $products->lastItem(),
        ], 'Products retrieved successfully');
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $rules = Product::validationRules();
        $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        $rules['stock_quantity'] = 'required|integer|min:0';
        $rules['min_stock_level'] = 'required|integer|min:0';

        $request->validate($rules);

        // Handle image upload
        $imageData = null;
        if ($request->hasFile('image')) {
            try {
                $imageData = $this->fileUploadService->uploadImage($request->file('image'), 'products');
            } catch (\InvalidArgumentException $e) {
                return $this->sendError('Image upload failed', ['image' => $e->getMessage()], 400);
            }
        }

        // Create product
        $productData = $request->only([
            'name',
            'description',
            'brand',
            'type',
            'color',
            'size',
            'gender',
            'selling_price',
            'purchase_price',
            'discount_type',
            'discount_value',
            'discount_start_date',
            'discount_end_date',
            'status'
        ]);

        if ($imageData) {
            $productData['image_url'] = $imageData['url'];
            $productData['image_path'] = $imageData['path'];
            $productData['thumbnail_url'] = $imageData['thumbnail_url'];
            $productData['thumbnail_path'] = $imageData['thumbnail_path'];
        }

        $product = Product::create($productData);

        // Create inventory record
        Inventory::create([
            'product_id' => $product->id,
            'stock_quantity' => $request->stock_quantity,
            'min_stock_level' => $request->min_stock_level,
        ]);

        // Load the inventory relationship
        $product->load('inventory');

        return $this->sendResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        $product->load('inventory');
        return $this->sendResponse($product, 'Product retrieved successfully');
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $rules = Product::validationRules();
        $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        $rules['stock_quantity'] = 'sometimes|integer|min:0';
        $rules['min_stock_level'] = 'sometimes|integer|min:0';

        $request->validate($rules);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image_path) {
                $this->fileUploadService->deleteImage($product->image_path, $product->thumbnail_path);
            }

            try {
                $imageData = $this->fileUploadService->uploadImage($request->file('image'), 'products');
                $product->image_url = $imageData['url'];
                $product->image_path = $imageData['path'];
                $product->thumbnail_url = $imageData['thumbnail_url'];
                $product->thumbnail_path = $imageData['thumbnail_path'];
            } catch (\InvalidArgumentException $e) {
                return $this->sendError('Image upload failed', ['image' => $e->getMessage()], 400);
            }
        }

        // Update product data
        $product->update($request->only([
            'name',
            'description',
            'brand',
            'type',
            'color',
            'size',
            'gender',
            'selling_price',
            'purchase_price',
            'discount_type',
            'discount_value',
            'discount_start_date',
            'discount_end_date',
            'status'
        ]));

        // Update inventory if provided
        if ($request->has('stock_quantity') || $request->has('min_stock_level')) {
            $inventoryData = [];
            if ($request->has('stock_quantity')) {
                $inventoryData['stock_quantity'] = $request->stock_quantity;
            }
            if ($request->has('min_stock_level')) {
                $inventoryData['min_stock_level'] = $request->min_stock_level;
            }

            if ($product->inventory) {
                $product->inventory->update($inventoryData);
            } else {
                $inventoryData['product_id'] = $product->id;
                Inventory::create($inventoryData);
            }
        }

        $product->load('inventory');

        return $this->sendResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        // Check if product has order items
        if ($product->orderItems()->exists()) {
            return $this->sendError(
                'Cannot delete product with existing orders',
                ['product' => 'This product has been ordered and cannot be deleted'],
                400
            );
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

        return $this->sendResponse([], 'Product deleted successfully');
    }

    /**
     * Get product categories (unique types).
     */
    public function categories()
    {
        $categories = Product::select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return $this->sendResponse($categories, 'Product categories retrieved successfully');
    }

    /**
     * Get product brands.
     */
    public function brands()
    {
        $brands = Product::select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return $this->sendResponse($brands, 'Product brands retrieved successfully');
    }

    /**
     * Get product colors.
     */
    public function colors()
    {
        $colors = Product::select('color')
            ->whereNotNull('color')
            ->distinct()
            ->orderBy('color')
            ->pluck('color');

        return $this->sendResponse($colors, 'Product colors retrieved successfully');
    }

    /**
     * Bulk update product status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'status' => 'required|in:active,inactive',
        ]);

        $updated = Product::whereIn('id', $request->product_ids)
            ->update(['status' => $request->status]);

        return $this->sendResponse([
            'updated_count' => $updated
        ], "Successfully updated {$updated} products to {$request->status} status");
    }
}
