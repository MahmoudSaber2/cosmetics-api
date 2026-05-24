<?php

namespace App\Http\Controllers\Api\V2\Website;

use App\Enums\DiscountTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Website\StoreOrderRequest;
use App\Http\Resources\V2\Website\OrderCollection;
use App\Http\Resources\V2\Website\OrderResource;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Website Orders",
 *     description="Order management for website customers"
 * )
 */
class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/website/orders",
     *     summary="Create a new order from website",
     *     description="Create a new order for website customers with automatic client creation if needed",
     *     operationId="createWebsiteOrder",
     *     tags={"Website Orders"},
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
     *             @OA\Property(property="name", type="string", description="Customer name", example="أحمد محمد"),
     *             @OA\Property(property="email", type="string", format="email", description="Customer email", example="ahmed@example.com"),
     *             @OA\Property(property="phone", type="string", description="Customer phone", example="+201234567890"),
     *             @OA\Property(property="address", type="string", description="Customer address", example="شارع النيل، المعادي"),
     *             @OA\Property(property="city", type="string", nullable=true, description="Customer city", example="القاهرة"),
     *             @OA\Property(property="note", type="string", nullable=true, description="Order notes", example="طلب عاجل"),
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
     *             @OA\Property(property="message", type="string", example="تم إنشاء الطلب بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="orderNumber", type="string", example="ORD-11202025-1234")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Product not available or out of stock",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="المنتج غير متوفر: Product Name"),
     *             @OA\Property(property="data", type="array", @OA\Items())
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
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="الاسم مطلوب")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="حدث خطأ أثناء إنشاء الطلب"),
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

            // Create or find client
            $client = Client::where('email', $data['email'])->first();

            if (!$client) {
                $client = Client::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'city' => $data['city'] ?? null,
                ]);
            }

            // Calculate totals and validate stock
            $totalAmount = 0;
            $totalCost = 0;
            $orderItems = [];

            foreach ($data['orderItems'] as $item) {
                $product = Product::with('inventory')->findOrFail($item['productId']);

                // Check if product is active and available
                if ($product->status !== ProductStatusEnum::ACTIVE) {
                    return ApiResponse::error(
                        __('messages.product_not_available'). $product->name,
                        [],
                        HttpStatusCode::BAD_REQUEST
                    );
                }

                // Check stock availability
                if (!$product->inventory || $product->inventory->quantity < $item['quantity']) {
                    return ApiResponse::error(
                        __('messages.product_out_of_stock'). $product->name,
                        [],
                        HttpStatusCode::BAD_REQUEST
                    );
                }


                $unitPrice = $product->price;
                $unitCost = $product->cost;
                $subtotal = $item['quantity'] * $unitPrice;
                $subtotalCost = $item['quantity'] * $unitCost;


                $totalAmount += $subtotal;
                $totalCost += $subtotalCost;

                $orderItems[] = [
                    'productId' => $item['productId'],
                    'quantity' => $item['quantity'],
                    'price' => $unitPrice,
                    'cost' => $unitCost,
                    'totalPrice' => $subtotal,
                    'totalCost' => $subtotalCost,
                ];
            }
            // Create order
            $order = Order::create([
                'client_id' => $client->id,
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
                'total_after_discount' => $totalAmount, // No discount for website orders initially
                'status' => OrderStatusEnum::PENDING,
                'note' => $data['note'] ?? null,
                'discount' => 0,
                'discount_type' => DiscountTypeEnum::NO_DISCOUNT,
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                OrderItem::create([
                    'product_id' => $itemData['productId'],
                    'order_id' => $order->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'cost' => $itemData['cost'],
                    'total_price' => $itemData['totalPrice'],
                    'total_cost' => $itemData['totalCost'],
                ]);
            }



            DB::commit();

            return ApiResponse::success(
                [
                    'orderNumber' => $order->number,
                ],
                __('messages.order_created_successfully'),
                HttpStatusCode::CREATED
            );

        } catch (\Exception $e) {
            DB::rollback();
            return ApiResponse::error(
                'حدث خطأ أثناء إنشاء الطلب',
                ['error' => $e->getMessage()],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Display the specified order by order number
     */
    public function show($orderNumber)
    {
        $order = Order::with(['client', 'orderItems.product.media'])
            ->where('number', $orderNumber)
            ->firstOrFail();

        return ApiResponse::success(new OrderResource($order));
    }

    /**
     * Track order by email and order number
     */
    public function track(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'orderNumber' => 'required|string',
        ]);

        $order = Order::with(['client', 'orderItems.product.media'])
            ->where('number', $request->orderNumber)
            ->whereHas('client', function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->first();

        if (!$order) {
            return ApiResponse::error(
                'لم يتم العثور على الطلب',
                [],
                HttpStatusCode::NOT_FOUND
            );
        }

        return ApiResponse::success(new OrderResource($order));
    }

    /**
     * Get client orders by email
     */
    public function clientOrders(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'perPage' => 'sometimes|integer|min:1|max:50',
        ]);

        $orders = Order::with(['client', 'orderItems.product.media'])
            ->whereHas('client', function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('perPage', 10));

        return ApiResponse::success(new OrderCollection($orders));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/website/orders/validate-cart",
     *     summary="Validate cart items before checkout",
     *     description="Validate cart items availability, stock, and calculate totals before placing an order",
     *     operationId="validateWebsiteCart",
     *     tags={"Website Orders"},
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
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Array of cart items to validate",
     *                 @OA\Items(
     *                     @OA\Property(property="productId", type="integer", description="Product ID", example=1),
     *                     @OA\Property(property="quantity", type="integer", description="Requested quantity", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart validation completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="isValid", type="boolean", example=true),
     *                 @OA\Property(property="totalAmount", type="number", format="float", example=200.00),
     *                 @OA\Property(
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="productId", type="integer", example=1),
     *                         @OA\Property(property="requestedQuantity", type="integer", example=2),
     *                         @OA\Property(property="isValid", type="boolean", example=true),
     *                         @OA\Property(property="errors", type="array", @OA\Items(type="string")),
     *                         @OA\Property(property="subtotal", type="number", format="float", example=200.00),
     *                         @OA\Property(property="unitPrice", type="number", format="float", example=100.00),
     *                         @OA\Property(
     *                             property="product",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="منتج تجريبي"),
     *                             @OA\Property(property="price", type="number", format="float", example=100.00),
     *                             @OA\Property(property="image", type="string", nullable=true, example="https://example.com/image.jpg"),
     *                             @OA\Property(property="availableQuantity", type="integer", example=10)
     *                         )
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
     *                     property="items",
     *                     type="array",
     *                     @OA\Items(type="string", example="يجب إضافة منتج واحد على الأقل")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function validateCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.productId' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $validationResults = [];
        $totalAmount = 0;
        $hasErrors = false;

        foreach ($request->items as $item) {
            $product = Product::with(['inventory', 'media'])->find($item['productId']);

            $itemResult = [
                'productId' => $item['productId'],
                'requestedQuantity' => $item['quantity'],
                'isValid' => true,
                'errors' => [],
                'product' => null,
            ];

            if (!$product) {
                $itemResult['isValid'] = false;
                $itemResult['errors'][] = 'المنتج غير موجود';
                $hasErrors = true;
            } else {
                // Check if product is active
                if ($product->status !== 'active') {
                    $itemResult['isValid'] = false;
                    $itemResult['errors'][] = 'المنتج غير متاح حالياً';
                    $hasErrors = true;
                }

                // Check stock availability
                if (!$product->inventory || $product->inventory->quantity < $item['quantity']) {
                    $itemResult['isValid'] = false;
                    $itemResult['errors'][] = 'الكمية المطلوبة غير متوفرة';
                    $itemResult['availableQuantity'] = $product->inventory?->quantity ?? 0;
                    $hasErrors = true;
                }

                if ($itemResult['isValid']) {
                    $subtotal = $item['quantity'] * $product->price;
                    $totalAmount += $subtotal;

                    $itemResult['subtotal'] = $subtotal;
                    $itemResult['unitPrice'] = $product->price;
                }

                $itemResult['product'] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'image' => $product->media ? $product->media->url : null,
                    'availableQuantity' => $product->inventory?->quantity ?? 0,
                ];
            }

            $validationResults[] = $itemResult;
        }

        return ApiResponse::success([
            'isValid' => !$hasErrors,
            'totalAmount' => $totalAmount,
            'items' => $validationResults,
        ]);
    }

    /**
     * Cancel order (only if pending)
     */
    public function cancel(Request $request, $orderNumber)
    {
        $request->validate([
            'email' => 'required|email',
            'reason' => 'sometimes|string|max:500',
        ]);

        $order = Order::where('number', $orderNumber)
            ->whereHas('client', function ($query) use ($request) {
                $query->where('email', $request->email);
            })
            ->first();

        if (!$order) {
            return ApiResponse::error(
                'لم يتم العثور على الطلب',
                [],
                HttpStatusCode::NOT_FOUND
            );
        }

        if ($order->status !== OrderStatusEnum::PENDING) {
            return ApiResponse::error(
                'لا يمكن إلغاء هذا الطلب',
                [],
                HttpStatusCode::BAD_REQUEST
            );
        }

        $order->update([
            'status' => OrderStatusEnum::REJECTED,
            'note' => ($order->note ? $order->note . ' | ' : '') . 'تم الإلغاء من العميل: ' . ($request->reason ?? 'بدون سبب'),
        ]);

        return ApiResponse::success([], 'تم إلغاء الطلب بنجاح');
    }
}
