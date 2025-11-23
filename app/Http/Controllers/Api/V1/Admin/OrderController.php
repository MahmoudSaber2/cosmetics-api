<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\DiscountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Order\FilterOrder;
use App\Filters\Order\FilterOrderDate;
use App\Helpers\ApiResponse;
use App\Http\Requests\V1\Order\StoreOrderRequest;
use App\Http\Resources\V1\Order\OrderCollection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Order\UpdateOrderRequest;
use App\Http\Resources\V1\Order\OrderResource;

class OrderController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of orders with optional filtering.
     */
    public function __construct()
    {

    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            // new Middleware('permission:all_orders', only:['index']),
            // new Middleware('permission:create_order', only:['store']),
            // new Middleware('permission:edit_order', only:['show']),
            // new Middleware('permission:update_order', only:['update']),
            // new Middleware('permission:delete_order', only:['destroy']),
        ];
    }
    public function index(Request $request)
    {
        $orders = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::exact('status', 'status'),
                AllowedFilter::exact('client', 'client_id'),
                AllowedFilter::custom('date', new FilterOrderDate()),
                AllowedFilter::custom('search', new FilterOrder()),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at', 'total_amount', 'status'])
            ->with(['client', 'orderItems.product'])
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new OrderCollection($orders));
    }

    /**
     * Store a newly created order (admin can create orders on behalf of clients).
     */
    public function store(StoreOrderRequest $request)
    {
        try {

            $data = $request->validated();

            DB::beginTransaction();

            // Create new client if provided
            $clientId = $request->clientId;
            if ($request->has('clientId') && $request->clientId === null) {
                $newClient = Client::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                ]);
                $clientId = $newClient->id;
            }

            // Calculate total amount
            $totalAmount = 0;
            $totalCost = 0;
            $orderItems = [];

            foreach ($data['orderItems'] as $item) {
                $product = Product::findOrFail($item['productId']);

                // Check stock availability
                if (!$product->inventory || $product->inventory->quantity < $item['quantity']) {
                    return ApiResponse::error(
                        'Insufficient stock for product: ' . $product->name,
                        [],
                        HttpStatusCode::BAD_REQUEST
                    );
                }

                $unitPrice = $product->price;
                $unitCost = $product->cost;

                $totalAmount += $item['quantity'] * $unitPrice;
                $totalCost += $item['quantity'] * $unitCost;

                $orderItems[] = [
                    'productId' => $item['productId'],
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
                    'cost' => $unitCost,
                    'subtotalCost' => $item['quantity'] * $unitCost,
                    'subtotal' => $item['quantity'] * $unitPrice,
                ];
            }

            $totalAfterDiscount = $totalAmount;

            if($data['discount'] ?? false) {
                if(($data['discount_type'] ?? null) === 'percentage') {
                    $discountAmount = ($totalAmount * $data['discount']) / 100;
                } else {
                    $discountAmount = $data['discount'];
                }
                $totalAfterDiscount = max(0, $totalAmount - $discountAmount);
            }
            // Create order
            $order = Order::create([
                'client_id' => $clientId,
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'status' => $data['status'],
                'note' => $data['note'],
                'discount' => $data['discount'] ?? 0,
                'discount_type' => $data['discountType'] ?? null,
                'total_after_discount' => $totalAfterDiscount
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $itemData['orderId'] = $order->id;
                OrderItem::create([
                    'order_id' => $itemData['orderId'],
                    'product_id' => $itemData['productId'],
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'cost' => $itemData['cost'],
                    'total_cost' => $itemData['subtotalCost'],
                    'total_price' => $itemData['subtotal'],
                ]);

                if($order->status === OrderStatusEnum::APPROVED || $order->status === OrderStatusEnum::COMPLETED) {
                    // Reduce stock
                    $product = Product::findOrFail($itemData['productId']);
                    $product->inventory->reduceStock($itemData['quantity']);
                }
            }

            DB::commit();

            return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error(__('messages.error'), [
                'error' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['client', 'orderItems.product.inventory']);
        return ApiResponse::success(new OrderResource($order));
    }

    /**
     * Update the specified order.
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        try{
            $data = $request->validated();

            DB::beginTransaction();

            $totalCost = 0;
            $totalAmount = 0;
            $totalAmountAfterDiscount = 0;

            $orderItems = [];
            foreach ($data['orderItems'] as $key => $item) {

                $product = Product::findOrFail($item['productId']);

                // Check stock availability
                if (!$product->inventory || $product->inventory->quantity < $item['quantity'] && $item['actionStatus'] != 3) {
                    return ApiResponse::error(
                        'Insufficient stock for product: ' . $product->name,
                        [],
                        HttpStatusCode::BAD_REQUEST
                    );
                }

                $unitPrice = $product->price;
                $unitCost = $product->cost;

                $totalAmount += $item['quantity'] * $unitPrice;
                $totalCost += $item['quantity'] * $unitCost;

                $orderItems[] = [
                    'orderItemId' => $item['orderItemId'] ?? null,
                    'productId' => $item['productId'],
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
                    'cost' => $unitCost,
                    'subtotalCost' => $item['quantity'] * $unitCost,
                    'subtotal' => $item['quantity'] * $unitPrice,
                    'actionStatus' => $item['actionStatus']
                ];

            }
            // Update order items logic can be added here
            foreach ($orderItems as $key => $item) {
                if($item['actionStatus'] == 3){ // Delete
                    $orderItem = OrderItem::findOrFail($item['orderItemId']);
                    $orderItem->delete();
                    continue;
                }
                if($item['actionStatus'] == 1){ // New Item
                    $product = Product::findOrFail($item['productId']);
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['productId'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'cost' => $product->cost,
                        'total_cost' => $item['quantity'] * $product->cost,
                        'total_price' => $item['quantity'] * $product->price,
                    ]);

                    $totalAmount += $item['quantity'] * $product->price;
                    $totalCost += $item['quantity'] * $product->cost;
                }

                if($item['actionStatus'] == 2){ // Update Item
                    $orderItem = OrderItem::findOrFail($item['orderItemId']);
                    $product = Product::findOrFail($item['productId']);
                    $orderItem->update([
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'cost' => $product->cost,
                        'total_cost' => $item['quantity'] * $product->cost,
                        'total_price' => $item['quantity'] * $product->price,
                    ]);
                    $totalAmount += $item['quantity'] * $product->price;
                    $totalCost += $item['quantity'] * $product->cost;
                }

                if(($order->status === OrderStatusEnum::APPROVED || $order->status === OrderStatusEnum::COMPLETED) && $item['actionStatus'] != 3){
                    // Reduce stock
                    $product = Product::findOrFail($item['productId']);
                    $product->inventory->reduceStock($item['quantity']);
                }
            }

            $totalAmountAfterDiscount = $totalAmount;

            if($data['discount'] ?? false) {
                if(($data['discountType'] ?? null) == DiscountTypeEnum::PERCENTAGE->value) {
                    $discountAmount = ($totalAmount * $data['discount']) / 100;
                } elseif(($data['discountType'] ?? null) == DiscountTypeEnum::FIXED->value) {
                    $discountAmount = $data['discount'];
                }
                $totalAmountAfterDiscount = max(0, $totalAmount - $discountAmount);
            } else {
                $totalAmountAfterDiscount = $totalAmount;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'status' => $data['status'],
                'note' => $data['note'],
                'discount' => $data['discount'] ?? 0,
                'discount_type' => $data['discountType'],
                'total_after_discount' => $totalAmountAfterDiscount
            ]);


            DB::commit();
            return ApiResponse::success([], 'Order updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error('Failed to update order', [
                'error' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    public function destroy(Order $order)
    {
        // Only allow deletion of pending or rejected orders
        if (!in_array($order->status, [OrderStatusEnum::PENDING, OrderStatusEnum::REJECTED])) {
            return ApiResponse::error(
                'Only pending or rejected orders can be deleted',
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        DB::beginTransaction();
        try {
            // Delete order items first
            $order->orderItems()->delete();

            // Delete the order
            $order->delete();

            DB::commit();

            return ApiResponse::success([], 'Order deleted successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error('Failed to delete order', ['error' => $e->getMessage()], HttpStatusCode::INTERNAL_SERVER_ERROR);
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
