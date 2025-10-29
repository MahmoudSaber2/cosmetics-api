<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends BaseApiController
{
    /**
     * Display a listing of clients with optional search and filtering.
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        // Filter by email domain
        if ($request->filled('email_domain')) {
            $query->where('email', 'like', "%@{$request->email_domain}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['name', 'email', 'city', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 100); // Max 100 items per page
        $clients = $query->paginate($perPage);

        return $this->sendResponse([
            'data' => $clients->items(),
            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'per_page' => $clients->perPage(),
            'total' => $clients->total(),
            'from' => $clients->firstItem(),
            'to' => $clients->lastItem(),
        ], 'Clients retrieved successfully');
    }

    /**
     * Store a newly created client.
     */
    public function store(Request $request)
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
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        return $this->sendResponse($client, 'Client retrieved successfully');
    }

    /**
     * Update the specified client.
     */
    public function update(Request $request, Client $client)
    {
        $rules = Client::validationRules();
        // Make email unique but ignore current client's email
        $rules['email'] = ['required', 'email', 'max:255', Rule::unique('clients')->ignore($client->id)];

        $request->validate($rules);

        $client->update($request->only([
            'name',
            'email',
            'phone',
            'address',
            'city'
        ]));

        return $this->sendResponse($client, 'Client updated successfully');
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Client $client)
    {
        // Check if client has orders
        if ($client->orders()->exists()) {
            return $this->sendError(
                'Cannot delete client with existing orders',
                ['client' => 'This client has orders and cannot be deleted'],
                400
            );
        }

        $client->delete();

        return $this->sendResponse([], 'Client deleted successfully');
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
