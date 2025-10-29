<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends BaseApiController
{
    /**
     * Create a new order.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'client' => 'required|array',
                'client.name' => 'required|string|max:255',
                'client.email' => 'required|email|max:255',
                'client.phone' => 'nullable|string|max:20',
                'client.address' => 'nullable|string|max:500',
                'client.city' => 'nullable|string|max:100',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:1000',
            ]);

            return DB::transaction(function () use ($request) {
                // Find or create client
                $clientData = $request->client;
                $client = Client::where('email', $clientData['email'])->first();

                if ($client) {
                    // Update existing client information
                    $client->update([
                        'name' => $clientData['name'],
                        'phone' => $clientData['phone'] ?? $client->phone,
                        'address' => $clientData['address'] ?? $client->address,
                        'city' => $clientData['city'] ?? $client->city,
                    ]);
                } else {
                    // Create new client
                    $client = Client::create($clientData);
                }

                // Validate products and stock availability
                $orderItems = [];
                $totalAmount = 0;

                foreach ($request->items as $item) {
                    $product = Product::with('inventory')->active()->find($item['product_id']);

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => ["Product with ID {$item['product_id']} is not available"]
                        ]);
                    }

                    // Check stock availability
                    if (!$product->inventory || $product->inventory->stock_quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => ["Insufficient stock for product '{$product->name}'. Available: {$product->getStockQuantity()}, Requested: {$item['quantity']}"]
                        ]);
                    }

                    $unitPrice = $product->price;
                    $subtotal = $unitPrice * $item['quantity'];
                    $totalAmount += $subtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'product' => $product, // Keep reference for inventory update
                    ];
                }

                // Create order
                $order = Order::create([
                    'client_id' => $client->id,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                // Create order items and update inventory
                foreach ($orderItems as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemData['subtotal'],
                    ]);

                    // Reserve stock (reduce inventory)
                    $inventory = $itemData['product']->inventory;
                    $inventory->stock_quantity -= $itemData['quantity'];
                    $inventory->save();
                }

                // Load relationships for response
                $order->load(['client', 'orderItems.product']);

                return $this->sendResponse([
                    'order' => [
                        'id' => $order->id,
                        'client' => [
                            'name' => $order->client->name,
                            'email' => $order->client->email,
                            'phone' => $order->client->phone,
                            'address' => $order->client->address,
                            'city' => $order->client->city,
                        ],
                        'items' => $order->orderItems->map(function ($item) {
                            return [
                                'product_id' => $item->product_id,
                                'product_name' => $item->product->name,
                                'product_brand' => $item->product->brand,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                                'subtotal' => $item->subtotal,
                            ];
                        }),
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'notes' => $order->notes,
                        'created_at' => $order->created_at,
                    ]
                ], 'Order created successfully', 201);
            });
        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Order creation failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get order status by order ID.
     */
    public function show($id)
    {
        $order = Order::with(['client', 'orderItems.product'])->find($id);

        if (!$order) {
            return $this->sendError('Order not found', [], 404);
        }

        return $this->sendResponse([
            'order' => [
                'id' => $order->id,
                'client' => [
                    'name' => $order->client->name,
                    'email' => $order->client->email,
                    'phone' => $order->client->phone,
                    'address' => $order->client->address,
                    'city' => $order->client->city,
                ],
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_brand' => $item->product->brand,
                        'product_image' => $item->product->image_url,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ];
                }),
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'notes' => $order->notes,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]
        ], 'Order retrieved successfully');
    }

    /**
     * Track orders by client email.
     */
    public function trackByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $client = Client::where('email', $request->email)->first();

        if (!$client) {
            return $this->sendError('No orders found', ['email' => 'No orders found for this email address'], 404);
        }

        $orders = Order::with(['orderItems.product'])
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return $this->sendError('No orders found', ['email' => 'No orders found for this email address'], 404);
        }

        $transformedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'items_count' => $order->orderItems->count(),
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return $this->sendResponse([
            'client' => [
                'name' => $client->name,
                'email' => $client->email,
            ],
            'orders' => $transformedOrders,
            'total_orders' => $orders->count(),
        ], 'Orders retrieved successfully');
    }

    /**
     * Validate cart items before order creation.
     */
    public function validateCart(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $validationResults = [];
            $totalAmount = 0;
            $hasErrors = false;

            foreach ($request->items as $item) {
                $product = Product::with('inventory')->active()->find($item['product_id']);

                $itemResult = [
                    'product_id' => $item['product_id'],
                    'requested_quantity' => $item['quantity'],
                    'is_valid' => true,
                    'errors' => [],
                ];

                if (!$product) {
                    $itemResult['is_valid'] = false;
                    $itemResult['errors'][] = 'Product is not available';
                    $hasErrors = true;
                } else {
                    $itemResult['product_name'] = $product->name;
                    $itemResult['product_price'] = $product->price;
                    $itemResult['available_stock'] = $product->getStockQuantity();

                    // Check stock availability
                    if (!$product->inventory || $product->inventory->stock_quantity < $item['quantity']) {
                        $itemResult['is_valid'] = false;
                        $itemResult['errors'][] = "Insufficient stock. Available: {$product->getStockQuantity()}";
                        $hasErrors = true;
                    } else {
                        $subtotal = $product->price * $item['quantity'];
                        $itemResult['unit_price'] = $product->price;
                        $itemResult['subtotal'] = $subtotal;
                        $totalAmount += $subtotal;
                    }
                }

                $validationResults[] = $itemResult;
            }

            return $this->sendResponse([
                'is_valid' => !$hasErrors,
                'items' => $validationResults,
                'total_amount' => $totalAmount,
                'summary' => [
                    'total_items' => count($request->items),
                    'valid_items' => count(array_filter($validationResults, fn($item) => $item['is_valid'])),
                    'invalid_items' => count(array_filter($validationResults, fn($item) => !$item['is_valid'])),
                ]
            ], 'Cart validation completed');
        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Cart validation failed', ['error' => 'Unable to validate cart'], 500);
        }
    }
}
