<?php

namespace App\Http\Controllers\Api\V2\Admin;

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
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Admin Orders",
 *     description="Order management operations for administrators"
 * )
 */
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

    /**
     * @OA\Get(
     *     path="/api/v1/admin/orders",
     *     summary="Get paginated list of orders with filtering",
     *     description="Retrieve a paginated list of orders with optional filtering by status, client, date, and search terms",
     *     operationId="getOrders",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter by order status (0=pending, 1=approved, 2=rejected, 3=completed)",
     *         required=false,
     *         @OA\Schema(type="integer", enum={0, 1, 2, 3})
     *     ),
     *     @OA\Parameter(
     *         name="filter[client]",
     *         in="query",
     *         description="Filter by client ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="filter[date]",
     *         in="query",
     *         description="Filter by date range (format: YYYY-MM-DD,YYYY-MM-DD or single date)",
     *         required=false,
     *         @OA\Schema(type="string", example="2024-01-01,2024-12-31")
     *     ),
     *     @OA\Parameter(
     *         name="filter[search]",
     *         in="query",
     *         description="Search in order number, client name, email, or phone",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field (created_at, total_amount, status). Prefix with - for descending",
     *         required=false,
     *         @OA\Schema(type="string", example="-created_at")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, minimum=1, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="orders",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="orderId", type="integer", example=1),
     *                         @OA\Property(property="number", type="string", example="ORD-2024-001"),
     *                         @OA\Property(property="totalCost", type="number", format="float", example=150.00),
     *                         @OA\Property(property="totalAmount", type="number", format="float", example=200.00),
     *                         @OA\Property(property="status", type="integer", example=0),
     *                         @OA\Property(property="totalAmountAfterDiscount", type="number", format="float", example=180.00),
     *                         @OA\Property(
     *                             property="client",
     *                             type="object",
     *                             @OA\Property(property="name", type="string", example="أحمد محمد"),
     *                             @OA\Property(property="email", type="string", example="ahmed@example.com")
     *                         ),
     *                         @OA\Property(property="createdAt", type="string", example="01/12/24 02:30 م")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=50),
     *                     @OA\Property(property="count", type="integer", example=15),
     *                     @OA\Property(property="perPage", type="integer", example=15),
     *                     @OA\Property(property="currentPage", type="integer", example=1),
     *                     @OA\Property(property="totalPages", type="integer", example=4)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
     * )
     */
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
     * @OA\Post(
     *     path="/api/v1/admin/orders",
     *     summary="Create a new order",
     *     description="Create a new order for existing or new client with order items",
     *     operationId="createOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="clientId", type="integer", nullable=true, description="Existing client ID (required if creating order for existing client)", example=1),
     *             @OA\Property(property="name", type="string", nullable=true, description="Client name (required if clientId is null)", example="أحمد محمد"),
     *             @OA\Property(property="email", type="string", nullable=true, description="Client email (required if clientId is null)", example="ahmed@example.com"),
     *             @OA\Property(property="phone", type="string", nullable=true, description="Client phone number", example="+201234567890"),
     *             @OA\Property(property="address", type="string", nullable=true, description="Client address", example="شارع النيل، المعادي"),
     *             @OA\Property(property="city", type="string", nullable=true, description="Client city", example="القاهرة"),
     *             @OA\Property(property="note", type="string", nullable=true, description="Order notes", example="طلب عاجل"),
     *             @OA\Property(property="status", type="integer", description="Order status (0=pending, 1=approved, 2=rejected, 3=completed)", example=0),
     *             @OA\Property(property="discount", type="number", format="float", nullable=true, description="Discount amount", example=20.00),
     *             @OA\Property(property="discountType", type="integer", nullable=true, description="Discount type (0=no_discount, 1=fixed, 2=percentage)", example=2),
     *             @OA\Property(
     *                 property="orderItems",
     *                 type="array",
     *                 description="Array of order items",
     *                 @OA\Items(
     *                     @OA\Property(property="productId", type="integer", description="Product ID", example=1),
     *                     @OA\Property(property="quantity", type="integer", description="Quantity", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order created successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Insufficient stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient stock for product: Product Name"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="orderItems",
     *                     type="array",
     *                     @OA\Items(type="string", example="")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Database connection failed")
     *             )
     *         )
     *     )
     * )
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
                $product = Product::find($item['productId']);
                // Check stock availability
                if ((!$product->inventory || $product->inventory->quantity < $item['quantity'] ) && $product->has_stock == true) {
                    return ApiResponse::error(
                        __('messages.product_out_of_stock') . $product->name,
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

                if(($order->status === OrderStatusEnum::APPROVED || $order->status === OrderStatusEnum::COMPLETED) && $product->has_stock == true) {
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
     * @OA\Get(
     *     path="/api/v1/admin/orders/{order}",
     *     summary="Get order details",
     *     description="Retrieve detailed information about a specific order including client and order items",
     *     operationId="getOrderDetails",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="orderId", type="integer", example=1),
     *                 @OA\Property(property="number", type="string", example="ORD-2024-001"),
     *                 @OA\Property(property="totalCost", type="number", format="float", example=150.00),
     *                 @OA\Property(property="totalAmount", type="number", format="float", example=200.00),
     *                 @OA\Property(property="status", type="integer", example=0),
     *                 @OA\Property(property="totalAmountAfterDiscount", type="number", format="float", example=180.00),
     *                 @OA\Property(property="discount", type="number", format="float", example=20.00),
     *                 @OA\Property(property="discountType", type="integer", example=2),
     *                 @OA\Property(
     *                     property="client",
     *                     type="object",
     *                     @OA\Property(property="clientId", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="أحمد محمد"),
     *                     @OA\Property(property="email", type="string", example="ahmed@example.com"),
     *                     @OA\Property(property="phone", type="string", example="+201234567890"),
     *                     @OA\Property(property="address", type="string", example="شارع النيل، المعادي"),
     *                     @OA\Property(property="city", type="string", example="القاهرة")
     *                 ),
     *                 @OA\Property(
     *                     property="orderItems",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="orderItemId", type="integer", example=1),
     *                         @OA\Property(property="quantity", type="integer", example=2),
     *                         @OA\Property(property="price", type="number", format="float", example=100.00),
     *                         @OA\Property(property="totalPrice", type="number", format="float", example=200.00),
     *                         @OA\Property(
     *                             property="product",
     *                             type="object",
     *                             @OA\Property(property="product", type="integer", example=1),
     *                             @OA\Property(property="productName", type="string", example="منتج تجريبي")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", example="01/12/24 02:30 م")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found")
     *         )
     *     )
     * )
     */
    public function show(Order $order)
    {
        $order->load(['client', 'orderItems.product.inventory']);
        return ApiResponse::success(new OrderResource($order));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/orders/{order}",
     *     summary="Update an existing order",
     *     description="Update order details including order items with action status (1=new, 2=update, 3=delete)",
     *     operationId="updateOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="clientId", type="integer", nullable=true, description="Existing client ID", example=1),
     *             @OA\Property(property="name", type="string", nullable=true, description="Client name (if clientId is null)", example="أحمد محمد"),
     *             @OA\Property(property="email", type="string", nullable=true, description="Client email (if clientId is null)", example="ahmed@example.com"),
     *             @OA\Property(property="phone", type="string", nullable=true, description="Client phone number", example="+201234567890"),
     *             @OA\Property(property="address", type="string", nullable=true, description="Client address", example="شارع النيل، المعادي"),
     *             @OA\Property(property="city", type="string", nullable=true, description="Client city", example="القاهرة"),
     *             @OA\Property(property="note", type="string", nullable=true, description="Order notes", example="طلب محدث"),
     *             @OA\Property(property="status", type="integer", description="Order status (0=pending, 1=approved, 2=rejected, 3=completed)", example=1),
     *             @OA\Property(property="discount", type="number", format="float", nullable=true, description="Discount amount", example=30.00),
     *             @OA\Property(property="discountType", type="integer", nullable=true, description="Discount type (0=no_discount, 1=fixed, 2=percentage)", example=1),
     *             @OA\Property(
     *                 property="orderItems",
     *                 type="array",
     *                 description="Array of order items with action status",
     *                 @OA\Items(
     *                     @OA\Property(property="orderItemId", type="integer", nullable=true, description="Order item ID (required for update/delete)", example=1),
     *                     @OA\Property(property="productId", type="integer", description="Product ID", example=1),
     *                     @OA\Property(property="quantity", type="integer", description="Quantity", example=3),
     *                     @OA\Property(property="actionStatus", type="integer", description="Action status (1=new, 2=update, 3=delete)", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order updated successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Insufficient stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Insufficient stock for product: Product Name"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="orderItems",
     *                     type="object",
     *                     example={}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update order"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="error", type="object", example={})
     *             )
     *         )
     *     )
     * )
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
                if ((!$product->inventory || $product->inventory->quantity < $item['quantity']) && $item['actionStatus'] != 3 && $product->has_stock == true) {
                    return ApiResponse::error(
                        __('messages.product_out_of_stock'). ' : ' . $product->name,
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
                        'product_id' => $item['productId'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'cost' => $product->cost,
                        'total_cost' => ($item['quantity'] * $product->cost),
                        'total_price' => $item['quantity'] * $product->price,
                    ]);
                    $totalAmount += $item['quantity'] * $product->price;
                    $totalCost += $item['quantity'] * $product->cost;
                }

                if(($order->status === OrderStatusEnum::APPROVED || $order->status === OrderStatusEnum::COMPLETED) && $item['actionStatus'] != 3 && $product->has_stock == true) {
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
            return ApiResponse::success([], __('messages.updated'));

        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error('Failed to update order', [
                'error' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/admin/orders/{order}",
     *     summary="Delete an order",
     *     description="Delete an order (only pending or rejected orders can be deleted)",
     *     operationId="deleteOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order deleted successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Cannot delete order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only pending or rejected orders can be deleted"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Order not found"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete order"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     )
     * )
     */
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
     * @OA\Put(
     *     path="/api/v1/admin/orders/{order}/approve",
     *     summary="Approve a pending order",
     *     description="Approve a pending order and reduce stock for all order items",
     *     operationId="approveOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order approved successfully"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Cannot approve order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only pending orders can be approved"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to approve order"),
     *             @OA\Property(property="data", type="object", example={}),
     *         )
     *     )
     * )
     */
    public function approve(Order $order)
    {
        if ($order->status !== OrderStatusEnum::PENDING) {
            return ApiResponse::error(
                __('messages.only_pending_orders_can_be_approved'),
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        DB::beginTransaction();
        try {
            // Check stock availability for all items
            foreach ($order->orderItems as $item) {
                if (!$item->product->inventory || $item->product->inventory->quantity < $item->quantity) {
                    return ApiResponse::error(
                        __('messages.product_out_of_stock'). $item->product->name,
                        [],
                        HttpStatusCode::BAD_REQUEST
                    );
                }
            }

            // Reduce stock for all items
            foreach ($order->orderItems as $item) {
                if ($item->product->has_stock == 1 && $item->product->inventory) {
                    $item->product->inventory->reduceStock('quantity', $item->quantity);
                }
            }

            $order->update(['status' => OrderStatusEnum::APPROVED]);

            DB::commit();

            return ApiResponse::success([], __('messages.order_approved_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error(__('messages.error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/orders/{order}/reject",
     *     summary="Reject a pending order",
     *     description="Reject a pending order with optional reason",
     *     operationId="rejectOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", nullable=true, description="Reason for rejection", example="Out of stock")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order rejected successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Cannot reject order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only pending orders can be rejected"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     )
     * )
     */
    public function reject(Request $request, Order $order)
    {
        if ($order->status !== OrderStatusEnum::PENDING) {
            return ApiResponse::error(
                __('messages.only_pending_orders_can_be_rejected'),
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => OrderStatusEnum::REJECTED,
            'rejectionReason' => $request->reason
        ]);

        return ApiResponse::success([], __('messages.order_rejected_successfully'));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/orders/{order}/complete",
     *     summary="Complete an approved order",
     *     description="Mark an approved order as completed",
     *     operationId="completeOrder",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         description="Order ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order completed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Cannot complete order",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Only approved orders can be completed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="order",
     *                     type="object",
     *                     example={}
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function complete(Order $order)
    {
        if ($order->status !== OrderStatusEnum::APPROVED) {
            return ApiResponse::error(
                __('messages.only_approved_orders_can_be_completed'),
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        $order->update(['status' => OrderStatusEnum::COMPLETED]);

        return ApiResponse::success(new OrderResource($order), __('messages.order_delivered_successfully'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/orders-statistics",
     *     summary="Get order statistics",
     *     description="Retrieve comprehensive order statistics including counts, revenue, and daily metrics",
     *     operationId="getOrderStatistics",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order statistics retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="totalOrders", type="integer", example=150),
     *                 @OA\Property(property="pendingOrders", type="integer", example=25),
     *                 @OA\Property(property="approvedOrders", type="integer", example=80),
     *                 @OA\Property(property="rejectedOrders", type="integer", example=15),
     *                 @OA\Property(property="completedOrders", type="integer", example=30),
     *                 @OA\Property(property="totalRevenue", type="number", format="float", example=45000.00),
     *                 @OA\Property(property="averageOrderValue", type="number", format="float", example=300.00),
     *                 @OA\Property(property="ordersToday", type="integer", example=5),
     *                 @OA\Property(property="revenueToday", type="number", format="float", example=1500.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function statistics()
    {
        $stats = [
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', OrderStatusEnum::PENDING)->count(),
            'approvedOrders' => Order::where('status', OrderStatusEnum::APPROVED)->count(),
            'rejectedOrders' => Order::where('status', OrderStatusEnum::REJECTED)->count(),
            'completedOrders' => Order::where('status', OrderStatusEnum::COMPLETED)->count(),
            'totalRevenue' => Order::whereIn('status', [OrderStatusEnum::APPROVED, OrderStatusEnum::COMPLETED])->sum('total_amount'),
            'averageOrderValue' => Order::whereIn('status', [OrderStatusEnum::APPROVED, OrderStatusEnum::COMPLETED])->avg('total_amount') ?? 0,
            'ordersToday' => Order::whereDate('created_at', today())->count(),
            'revenueToday' => Order::whereDate('created_at', today())
                ->whereIn('status', [OrderStatusEnum::APPROVED, OrderStatusEnum::COMPLETED])
                ->sum('total_amount'),
        ];

        return ApiResponse::success($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/orders-status-counts",
     *     summary="Get order counts by status",
     *     description="Retrieve the count of orders grouped by their status",
     *     operationId="getOrderStatusCounts",
     *     tags={"Admin Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status counts retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Order status counts retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="pending", type="integer", example=25),
     *                 @OA\Property(property="approved", type="integer", example=80),
     *                 @OA\Property(property="rejected", type="integer", example=15),
     *                 @OA\Property(property="completed", type="integer", example=30)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function statusCounts()
    {
        $counts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are present
        $allStatuses = [
            OrderStatusEnum::PENDING->value => 'pending',
            OrderStatusEnum::APPROVED->value => 'approved',
            OrderStatusEnum::REJECTED->value => 'rejected',
            OrderStatusEnum::COMPLETED->value => 'completed'
        ];

        $formattedCounts = [];
        foreach ($allStatuses as $statusValue => $statusName) {
            $formattedCounts[$statusName] = $counts[$statusValue] ?? 0;
        }

        return ApiResponse::success($formattedCounts);
    }
}
