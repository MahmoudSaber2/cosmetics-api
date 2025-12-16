<?php

namespace App\Http\Controllers\Api\V2\Admin;

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
use OpenApi\Attributes as OA;

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
     * @OA\Get(
     *     path="/api/v1/admin/clients",
     *     operationId="getAllClients",
     *     summary="Get all clients",
     *     description="Retrieve a paginated list of clients with optional search filter.",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="filter[search]",
     *         in="query",
     *         description="Search by client name, email, or phone",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Clients retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="clients",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="clientId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="phone", type="string", example="1234567890"),
     *                         @OA\Property(property="address", type="string", example="123 Main St"),
     *                         @OA\Property(property="city", type="string", example="New York"),
     *                         @OA\Property(property="createdAt", type="string", example="24/11/2025 02:15 PM"),
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="count", type="integer", example=15),
     *                     @OA\Property(property="perPage", type="integer", example=15),
     *                     @OA\Property(property="currentPage", type="integer", example=1),
     *                     @OA\Property(property="totalPages", type="integer", example=7)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to view clients",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database or system error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while processing your request."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/admin/clients/{id}",
     *     operationId="showClient",
     *     summary="Get client details",
     *     description="Retrieve detailed information about a specific client by ID.",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="clientId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", example="1234567890"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="city", type="string", example="New York")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Client not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to view client details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function show(Client $client)
    {
        return ApiResponse::success(new ClientResource($client));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/clients/{id}",
     *     operationId="updateClient",
     *     summary="Update client information",
     *     description="Updates an existing client's information.",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "email"},
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="0987654321"),
     *                 @OA\Property(property="address", type="string", nullable=true, example="456 Updated St"),
     *                 @OA\Property(property="city", type="string", nullable=true, example="Los Angeles")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client updated successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     type="array",
     *                     @OA\Items(type="string", example="The email has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Client not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to update clients",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the client."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $client->update($data);

        return ApiResponse::success([], __('messages.updated'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/clients/{id}",
     *     operationId="deleteClient",
     *     summary="Delete a client",
     *     description="Permanently delete a client from the system. Cannot delete clients with existing orders.",
     *     tags={"Clients"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         required=false,
     *         description="Language preference for response messages",
     *         @OA\Schema(type="string", enum={"ar", "en"}, example="ar")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client deleted successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete client with existing orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete client with existing orders."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Client not found."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Missing or invalid authentication token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Insufficient permissions to delete clients",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
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

        return ApiResponse::success([
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

        return ApiResponse::success([
            'client' => $client,
            'statistics' => $stats
        ], 'Client statistics retrieved successfully');
    }
}
