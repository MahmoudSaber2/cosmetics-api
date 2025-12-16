<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Enums\OrderStatusEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Admin Dashboard",
 *     description="Dashboard analytics and overview data for administrators"
 * )
 */
class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            // new Middleware('permission:view_dashboard', only:['index']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/dashboard",
     *     summary="Get dashboard overview data",
     *     description="Retrieve comprehensive dashboard data including statistics, latest orders, order status chart, and low stock products",
     *     operationId="getDashboardData",
     *     tags={"Admin Dashboard"},
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
     *         description="Dashboard data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="statistics",
     *                     type="object",
     *                     @OA\Property(property="totalProducts", type="integer", example=150),
     *                     @OA\Property(property="totalOrders", type="integer", example=320),
     *                     @OA\Property(property="totalOrdersToday", type="integer", example=12),
     *                     @OA\Property(property="pendingOrders", type="integer", example=25),
     *                     @OA\Property(property="totalRevenue", type="number", format="float", example=45000.00),
     *                     @OA\Property(property="totalRevenueToday", type="number", format="float", example=2500.00)
     *                 ),
     *                 @OA\Property(
     *                     property="latestOrders",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="orderNumber", type="string", example="ORD-11202025-1234"),
     *                         @OA\Property(property="clientName", type="string", example="أحمد محمد"),
     *                         @OA\Property(property="clientPhone", type="string", example="+201234567890"),
     *                         @OA\Property(property="totalPrice", type="number", format="float", example=250.00),
     *                         @OA\Property(property="status", type="integer", example=0),
     *                         @OA\Property(property="statusText", type="string", example="معلق"),
     *                         @OA\Property(property="createdAt", type="string", example="01/12/24 02:30 م")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="orderStatusChart",
     *                     type="object",
     *                     @OA\Property(property="pending", type="number", format="float", example=25.5),
     *                     @OA\Property(property="approved", type="number", format="float", example=40.2),
     *                     @OA\Property(property="rejected", type="number", format="float", example=10.1),
     *                     @OA\Property(property="completed", type="number", format="float", example=24.2)
     *                 ),
     *                 @OA\Property(
     *                     property="lowStockProducts",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="productId", type="integer", example=1),
     *                         @OA\Property(property="productName", type="string", example="منتج تجريبي"),
     *                         @OA\Property(property="currentStock", type="integer", example=5),
     *                         @OA\Property(property="minStock", type="integer", example=10),
     *                         @OA\Property(property="stockStatus", type="string", example="منخفض"),
     *                         @OA\Property(property="brandName", type="string", example="براند تجريبي"),
     *                         @OA\Property(property="categoryName", type="string", example="فئة تجريبية")
     *                     )
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
        try {
            // 1. Statistics
            $statistics = $this->getStatistics();

            // 2. Latest 5 Orders
            $latestOrders = $this->getLatestOrders();

            // 3. Order Status Chart (Percentages)
            $orderStatusChart = $this->getOrderStatusChart();

            // 4. Low Stock Products
            $lowStockProducts = $this->getLowStockProducts();

            $dashboardData = [
                'statistics' => $statistics,
                'latestOrders' => $latestOrders,
                'orderStatusChart' => $orderStatusChart,
                'lowStockProducts' => $lowStockProducts
            ];

            return ApiResponse::success($dashboardData);

        } catch (\Exception $e) {
            return ApiResponse::error(__('messages.error'), [
                'error' => $e->getMessage()
            ], HttpStatusCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getStatistics(): array
    {
        $today = Carbon::today();

        return [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'totalOrdersToday' => Order::whereDate('created_at', $today)->count(),
            'pendingOrders' => Order::where('status', OrderStatusEnum::PENDING)->count(),
            'totalRevenue' => Order::whereIn('status', [OrderStatusEnum::APPROVED, OrderStatusEnum::COMPLETED])
                ->sum('total_after_discount'),
            'totalRevenueToday' => Order::whereDate('created_at', $today)
                ->whereIn('status', [OrderStatusEnum::APPROVED, OrderStatusEnum::COMPLETED])
                ->sum('total_after_discount')
        ];
    }

    /**
     * Get latest 5 orders
     */
    private function getLatestOrders(): array
    {
        $orders = Order::with(['client'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return $orders->map(function ($order) {
            return [
                'orderNumber' => $order->number,
                'clientName' => $order->client->name,
                'clientPhone' => $order->client->phone ?? '',
                'totalPrice' => $order->total_after_discount ?? $order->total_amount,
                'status' => $order->status->value,
                'statusText' => $this->getStatusText($order->status),
                'createdAt' => Carbon::parse($order->created_at)->translatedFormat('d/m/y h:i A')
            ];
        })->toArray();
    }

    /**
     * Get order status chart data in percentages
     */
    private function getOrderStatusChart(): array
    {
        $totalOrders = Order::count();

        if ($totalOrders === 0) {
            return [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'completed' => 0
            ];
        }

        $statusCounts = [
            'pending' => Order::where('status', OrderStatusEnum::PENDING)->count(),
            'approved' => Order::where('status', OrderStatusEnum::APPROVED)->count(),
            'rejected' => Order::where('status', OrderStatusEnum::REJECTED)->count(),
            'completed' => Order::where('status', OrderStatusEnum::COMPLETED)->count()
        ];

        return [
            'pending' => round(($statusCounts['pending'] / $totalOrders) * 100, 1),
            'approved' => round(($statusCounts['approved'] / $totalOrders) * 100, 1),
            'rejected' => round(($statusCounts['rejected'] / $totalOrders) * 100, 1),
            'completed' => round(($statusCounts['completed'] / $totalOrders) * 100, 1)
        ];
    }

    /**
     * Get products with low stock or at minimum stock level
     */
    private function getLowStockProducts(): array
    {
        $products = Product::with(['inventory', 'brand', 'category'])
            ->where('has_stock', true)
            ->whereHas('inventory', function ($query) {
                $query->whereRaw('quantity <= products.min_stock');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $products->map(function ($product) {
            $stockStatus = 'نفد المخزون';
            if ($product->inventory && $product->inventory->quantity > 0) {
                $stockStatus = $product->inventory->quantity <= $product->min_stock ? 'منخفض' : 'متوفر';
            }

            return [
                'productId' => $product->id,
                'productName' => $product->name,
                'currentStock' => $product->inventory ? $product->inventory->quantity : 0,
                'minStock' => $product->min_stock,
                'stockStatus' => $stockStatus,
                'brandName' => $product->brand ? $product->brand->name : '',
                'categoryName' => $product->category ? $product->category->name : ''
            ];
        })->toArray();
    }

    /**
     * Get status text in Arabic
     */
    private function getStatusText(OrderStatusEnum $status): string
    {
        return match ($status) {
            OrderStatusEnum::PENDING => 'معلق',
            OrderStatusEnum::APPROVED => 'موافق عليه',
            OrderStatusEnum::REJECTED => 'مرفوض',
            OrderStatusEnum::COMPLETED => 'مكتمل',
        };
    }
}
