<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseApiController
{
    /**
     * Display a listing of orders with optional filtering.
     */
    public function index(Request $request)
    {
        $query = Order::with(['client', 'orderItems.product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Search by client name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['total_amount', 'status', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100);
        $orders = $query->paginate($perPage);

        return $this->sendResponse([
            'data' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ], 'Orders retrieved successfully');
    }

    /**
     * Store a newly created order (admin can create orders on behalf of clients).
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required_without:new_client|exists:clients,id',
            'new_client' => 'required_without:client_id|array',
            'new_client.name' => 'required_with:new_client|string|max:255',
            'new_client.email' => 'required_with:new_client|email|max:255|unique:clients,email',
            'new_client.phone' => 'required_with:new_client|string|max:20',
            'new_client.address' => 'required_with:new_client|string|max:500',
            'new_client.city' => 'required_with:new_client|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Create new client if provided
            $clientId = $request->client_id;
            if ($request->has('new_client')) {
                $newClient = Client::create([
                    'name' => $request->new_client['name'],
                    'email' => $request->new_client['email'],
                    'phone' => $request->new_client['phone'],
                    'address' => $request->new_client['address'],
                    'city' => $request->new_client['city'],
                ]);
                $clientId = $newClient->id;
            }

            // Calculate total amount
            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock availability
                if (!$product->inventory || $product->inventory->stock_quantity < $item['quantity']) {
                    return $this->sendError(
                        'Insufficient stock for product: ' . $product->name,
                        ['stock' => 'Not enough stock available'],
                        400
                    );
                }

                $unitPrice = $product->price;
                $subtotal = $item['quantity'] * $unitPrice;
                $totalAmount += $subtotal;

                $orderItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];
            }

            // Create order
            $order = Order::create([
                'client_id' => $clientId,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            $order->load(['client', 'orderItems.product']);

            DB::commit();

            return $this->sendResponse($order, 'Order created successfully', 201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed to create order', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['client', 'orderItems.product.inventory']);
        return $this->sendResponse($order, 'Order retrieved successfully');
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'sometimes|in:pending,approved,rejected,completed',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        // Only allow updates if order is still pending
        if ($order->status !== 'pending' && $request->has('items')) {
            return $this->sendError(
                'Cannot modify items of non-pending orders',
                ['order' => 'Order items can only be modified when status is pending'],
                400
            );
        }

        DB::beginTransaction();
        try {
            // Update order items if provided
            if ($request->has('items')) {
                // Delete existing order items
                $order->orderItems()->delete();

                // Calculate new total amount
                $totalAmount = 0;

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    // Check stock availability
                    if (!$product->inventory || $product->inventory->stock_quantity < $item['quantity']) {
                        return $this->sendError(
                            'Insufficient stock for product: ' . $product->name,
                            ['stock' => 'Not enough stock available'],
                            400
                        );
                    }

                    $unitPrice = $product->price;
                    $subtotal = $item['quantity'] * $unitPrice;
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                }

                $order->total_amount = $totalAmount;
            }

            // Update order status and notes
            if ($request->has('status')) {
                $order->status = $request->status;
            }
            if ($request->has('notes')) {
                $order->notes = $request->notes;
            }

            $order->save();
            $order->load(['client', 'orderItems.product']);

            DB::commit();

            return $this->sendResponse($order, 'Order updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed to update order', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order)
    {
        // Only allow deletion of pending or rejected orders
        if (!in_array($order->status, ['pending', 'rejected'])) {
            return $this->sendError(
                'Cannot delete approved or completed orders',
                ['order' => 'Only pending or rejected orders can be deleted'],
                400
            );
        }

        DB::beginTransaction();
        try {
            // Delete order items first
            $order->orderItems()->delete();

            // Delete the order
            $order->delete();

            DB::commit();

            return $this->sendResponse([], 'Order deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed to delete order', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve an order.
     */
    public function approve(Order $order)
    {
        if ($order->status !== 'pending') {
            return $this->sendError(
                'Only pending orders can be approved',
                ['order' => 'Order must be in pending status to approve'],
                400
            );
        }

        DB::beginTransaction();
        try {
            // Check stock availability for all items
            foreach ($order->orderItems as $item) {
                if (!$item->product->inventory || $item->product->inventory->stock_quantity < $item->quantity) {
                    return $this->sendError(
                        'Insufficient stock for product: ' . $item->product->name,
                        ['stock' => 'Not enough stock available'],
                        400
                    );
                }
            }

            // Reduce stock for all items
            foreach ($order->orderItems as $item) {
                $item->product->inventory->reduceStock($item->quantity);
            }

            $order->approve();
            $order->load(['client', 'orderItems.product']);

            DB::commit();

            return $this->sendResponse($order, 'Order approved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->sendError('Failed to approve order', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject an order.
     */
    public function reject(Request $request, Order $order)
    {
        if ($order->status !== 'pending') {
            return $this->sendError(
                'Only pending orders can be rejected',
                ['order' => 'Order must be in pending status to reject'],
                400
            );
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $order->reject($request->reason);
        $order->load(['client', 'orderItems.product']);

        return $this->sendResponse($order, 'Order rejected successfully');
    }

    /**
     * Complete an order.
     */
    public function complete(Order $order)
    {
        if ($order->status !== 'approved') {
            return $this->sendError(
                'Only approved orders can be completed',
                ['order' => 'Order must be in approved status to complete'],
                400
            );
        }

        $order->complete();
        $order->load(['client', 'orderItems.product']);

        return $this->sendResponse($order, 'Order completed successfully');
    }

    /**
     * Get order statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::pending()->count(),
            'approved_orders' => Order::approved()->count(),
            'rejected_orders' => Order::rejected()->count(),
            'completed_orders' => Order::completed()->count(),
            'total_revenue' => Order::whereIn('status', ['approved', 'completed'])->sum('total_amount'),
            'average_order_value' => Order::whereIn('status', ['approved', 'completed'])->avg('total_amount') ?? 0,
            'orders_today' => Order::whereDate('created_at', today())->count(),
            'revenue_today' => Order::whereDate('created_at', today())
                ->whereIn('status', ['approved', 'completed'])
                ->sum('total_amount'),
        ];

        return $this->sendResponse($stats, 'Order statistics retrieved successfully');
    }

    /**
     * Get orders by status counts.
     */
    public function statusCounts()
    {
        $counts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present
        $allStatuses = ['pending', 'approved', 'rejected', 'completed'];
        foreach ($allStatuses as $status) {
            if (!isset($counts[$status])) {
                $counts[$status] = 0;
            }
        }

        return $this->sendResponse($counts, 'Order status counts retrieved successfully');
    }
}
