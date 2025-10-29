<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseApiController
{
    /**
     * Display a listing of inventory with optional filtering.
     */
    public function index(Request $request)
    {
        $query = Inventory::with(['product']);

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->inStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
            }
        }

        // Filter by product name or brand
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter by product brand
        if ($request->filled('brand')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand', $request->brand);
            });
        }

        // Filter by product type
        if ($request->filled('type')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Filter by product status
        if ($request->filled('product_status')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('status', $request->product_status);
            });
        }

        // Filter by stock quantity range
        if ($request->filled('min_stock')) {
            $query->where('stock_quantity', '>=', $request->min_stock);
        }
        if ($request->filled('max_stock')) {
            $query->where('stock_quantity', '<=', $request->max_stock);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'updated_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['stock_quantity', 'min_stock_level', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } elseif ($sortBy === 'product_name') {
            $query->join('products', 'inventory.product_id', '=', 'products.id')
                ->orderBy('products.name', $sortOrder === 'asc' ? 'asc' : 'desc')
                ->select('inventory.*');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $inventory = $query->paginate($perPage);

        return $this->sendResponse([
            'data' => $inventory->items(),
            'current_page' => $inventory->currentPage(),
            'last_page' => $inventory->lastPage(),
            'per_page' => $inventory->perPage(),
            'total' => $inventory->total(),
            'from' => $inventory->firstItem(),
            'to' => $inventory->lastItem(),
        ], 'Inventory retrieved successfully');
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Inventory $inventory)
    {
        $inventory->load('product');
        return $this->sendResponse($inventory, 'Inventory item retrieved successfully');
    }

    /**
     * Update the specified inventory item.
     */
    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'stock_quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
        ]);

        $inventory->update($request->only(['stock_quantity', 'min_stock_level']));
        $inventory->load('product');

        return $this->sendResponse($inventory, 'Inventory updated successfully');
    }

    /**
     * Update stock quantity for a specific product.
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'operation' => 'sometimes|in:set,add,subtract',
        ]);

        $inventory = $product->inventory;
        if (!$inventory) {
            return $this->sendError(
                'Inventory record not found for this product',
                ['inventory' => 'Product does not have an inventory record'],
                404
            );
        }

        $operation = $request->get('operation', 'set');
        $quantity = $request->stock_quantity;

        switch ($operation) {
            case 'add':
                $inventory->increaseStock($quantity);
                break;
            case 'subtract':
                if ($inventory->stock_quantity < $quantity) {
                    return $this->sendError(
                        'Insufficient stock to subtract',
                        ['stock' => 'Cannot subtract more than current stock'],
                        400
                    );
                }
                $inventory->reduceStock($quantity);
                break;
            case 'set':
            default:
                $inventory->setStock($quantity);
                break;
        }

        $inventory->load('product');

        return $this->sendResponse($inventory, 'Stock updated successfully');
    }

    /**
     * Get low stock alerts.
     */
    public function lowStockAlerts()
    {
        $lowStockItems = Inventory::with('product')
            ->lowStock()
            ->whereHas('product', function ($q) {
                $q->where('status', 'active');
            })
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return $this->sendResponse($lowStockItems, 'Low stock alerts retrieved successfully');
    }

    /**
     * Get out of stock items.
     */
    public function outOfStockItems()
    {
        $outOfStockItems = Inventory::with('product')
            ->outOfStock()
            ->whereHas('product', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        return $this->sendResponse($outOfStockItems, 'Out of stock items retrieved successfully');
    }

    /**
     * Get inventory statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_products' => Inventory::count(),
            'in_stock_products' => Inventory::inStock()->count(),
            'out_of_stock_products' => Inventory::outOfStock()->count(),
            'low_stock_products' => Inventory::lowStock()->count(),
            'total_stock_value' => DB::table('inventory')
                ->join('products', 'inventory.product_id', '=', 'products.id')
                ->sum(DB::raw('inventory.stock_quantity * products.price')),
            'average_stock_level' => Inventory::avg('stock_quantity') ?? 0,
            'products_needing_restock' => Inventory::lowStock()
                ->whereHas('product', function ($q) {
                    $q->where('status', 'active');
                })
                ->count(),
        ];

        return $this->sendResponse($stats, 'Inventory statistics retrieved successfully');
    }

    /**
     * Bulk update stock quantities.
     */
    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'updates' => 'required|array|min:1',
            'updates.*.product_id' => 'required|exists:products,id',
            'updates.*.stock_quantity' => 'required|integer|min:0',
            'updates.*.operation' => 'sometimes|in:set,add,subtract',
        ]);

        DB::beginTransaction();
        try {
            $updatedCount = 0;
            $errors = [];

            foreach ($request->updates as $index => $update) {
                $product = Product::find($update['product_id']);
                $inventory = $product->inventory;

                if (!$inventory) {
                    $errors[] = "Product ID {$update['product_id']} does not have an inventory record";
                    continue;
                }

                $operation = $update['operation'] ?? 'set';
                $quantity = $update['stock_quantity'];

                switch ($operation) {
                    case 'add':
                        $inventory->increaseStock($quantity);
                        break;
                    case 'subtract':
                        if ($inventory->stock_quantity < $quantity) {
                            $errors[] = "Product ID {$update['product_id']}: Insufficient stock to subtract {$quantity}";
                            continue 2;
                        }
                        $inventory->reduceStock($quantity);
                        break;
                    case 'set':
                    default:
                        $inventory->setStock($quantity);
                        break;
                }

                $updatedCount++;
            }

            if (!empty($errors)) {
                DB::rollback();
                return $this->sendError(
                    'Bulk update failed',
                    ['errors' => $errors],
                    400
                );
            }

            DB::commit();

            return $this->sendResponse([
                'updated_count' => $updatedCount,
                'total_requested' => count($request->updates)
            ], "Successfully updated stock for {$updatedCount} products");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Bulk update failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get stock movement history (simplified version).
     */
    public function stockMovements(Request $request)
    {
        // This is a simplified version - in a real application, you'd have a separate
        // stock_movements table to track all stock changes with timestamps and reasons

        $query = Inventory::with('product');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $inventory = $query->orderBy('updated_at', 'desc')->get();

        // Transform to show recent updates as "movements"
        $movements = $inventory->map(function ($item) {
            return [
                'id' => $item->id,
                'product' => $item->product,
                'current_stock' => $item->stock_quantity,
                'min_stock_level' => $item->min_stock_level,
                'last_updated' => $item->updated_at,
                'status' => $item->isOutOfStock() ? 'out_of_stock' : ($item->isLowStock() ? 'low_stock' : 'in_stock'),
            ];
        });

        return $this->sendResponse($movements, 'Stock movements retrieved successfully');
    }

    /**
     * Generate inventory report.
     */
    public function report(Request $request)
    {
        $request->validate([
            'format' => 'sometimes|in:summary,detailed',
            'include_inactive' => 'sometimes|boolean',
        ]);

        $format = $request->get('format', 'summary');
        $includeInactive = $request->get('include_inactive', false);

        $query = Inventory::with('product');

        if (!$includeInactive) {
            $query->whereHas('product', function ($q) {
                $q->where('status', 'active');
            });
        }

        $inventory = $query->get();

        if ($format === 'detailed') {
            $report = [
                'generated_at' => now(),
                'total_items' => $inventory->count(),
                'summary' => [
                    'in_stock' => $inventory->filter(fn($item) => $item->isInStock())->count(),
                    'out_of_stock' => $inventory->filter(fn($item) => $item->isOutOfStock())->count(),
                    'low_stock' => $inventory->filter(fn($item) => $item->isLowStock())->count(),
                ],
                'items' => $inventory->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'brand' => $item->product->brand,
                        'type' => $item->product->type,
                        'current_stock' => $item->stock_quantity,
                        'min_stock_level' => $item->min_stock_level,
                        'status' => $item->isOutOfStock() ? 'out_of_stock' : ($item->isLowStock() ? 'low_stock' : 'in_stock'),
                        'stock_value' => $item->stock_quantity * $item->product->price,
                    ];
                }),
            ];
        } else {
            $report = [
                'generated_at' => now(),
                'summary' => [
                    'total_products' => $inventory->count(),
                    'in_stock' => $inventory->filter(fn($item) => $item->isInStock())->count(),
                    'out_of_stock' => $inventory->filter(fn($item) => $item->isOutOfStock())->count(),
                    'low_stock' => $inventory->filter(fn($item) => $item->isLowStock())->count(),
                    'total_stock_value' => $inventory->sum(fn($item) => $item->stock_quantity * $item->product->price),
                    'average_stock_level' => $inventory->avg('stock_quantity'),
                ],
            ];
        }

        return $this->sendResponse($report, 'Inventory report generated successfully');
    }
}
