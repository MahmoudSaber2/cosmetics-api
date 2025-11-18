<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Client\FilterClient;
use App\Helpers\ApiResponse;
use App\Http\Resources\V1\Client\ClientCollection;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Client\UpdateClientRequest;
use App\Http\Resources\V1\Client\ClientResource;

class ClientController extends Controller implements HasMiddleware
{
    public function __construct()
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('permission:all_clients', only:['index']),
            new Middleware('permission:create_client', only:['store']),
            new Middleware('permission:edit_client', only:['show']),
            new Middleware('permission:update_client', only:['update']),
            new Middleware('permission:delete_client', only:['destroy']),
        ];
    }

    /**
     * Display a listing of clients with optional search and filtering.
     */
    public function index(Request $request)
    {

        $clients = QueryBuilder::for(Client::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterClient()),
            ])
            ->defaultSort('-created_at')
            ->allowedSorts(['created_at'])
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new ClientCollection($clients));

    }

    /**
     * Store a newly created client.
     */
    /*public function store(Request $request)
    {
        $request->validate(Client::validationRules());

        $client = Client::create($request->only([
            'name',
            'email',
            'phone',
            'address',
            'city'
        ]));

        return $this->sendResponse($client, 'Client created successfully', 201);
    }*/

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        return ApiResponse::success(new ClientResource($client));
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $client->update($data);

        return ApiResponse::success([], __('messages.updated'));
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client)
    {
        // Check if client has orders
        if ($client->orders()->exists()) {
            return ApiResponse::error(__('messages.client_has_orders'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }

        $client->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }

    /**
     * Get client's order history.
     */
    public function orders(Client $client)
    {
        $orders = $client->orders()
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse([
            'client' => $client,
            'orders' => $orders
        ], 'Client order history retrieved successfully');
    }

    /**
     * Get client statistics.
     */
    public function statistics(Client $client)
    {
        $stats = [
            'total_orders' => $client->orders()->count(),
            'pending_orders' => $client->orders()->where('status', 'pending')->count(),
            'approved_orders' => $client->orders()->where('status', 'approved')->count(),
            'rejected_orders' => $client->orders()->where('status', 'rejected')->count(),
            'completed_orders' => $client->orders()->where('status', 'completed')->count(),
            'total_spent' => $client->orders()
                ->whereIn('status', ['approved', 'completed'])
                ->sum('total_amount'),
            'average_order_value' => $client->orders()
                ->whereIn('status', ['approved', 'completed'])
                ->avg('total_amount') ?? 0,
            'first_order_date' => $client->orders()->min('created_at'),
            'last_order_date' => $client->orders()->max('created_at'),
        ];

        return $this->sendResponse([
            'client' => $client,
            'statistics' => $stats
        ], 'Client statistics retrieved successfully');
    }
}
