<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientController extends BaseApiController
{
    /**
     * Register a new client.
     */
    public function store(Request $request)
    {
        try {
            $rules = Client::validationRules();

            // For public registration, we allow duplicate emails but will find existing client
            $rules['email'] = 'required|email|max:255';

            $request->validate($rules);

            // Check if client already exists by email
            $existingClient = Client::where('email', $request->email)->first();

            if ($existingClient) {
                // Update existing client information if provided
                $updateData = [];

                if ($request->filled('name') && $request->name !== $existingClient->name) {
                    $updateData['name'] = $request->name;
                }

                if ($request->filled('phone') && $request->phone !== $existingClient->phone) {
                    $updateData['phone'] = $request->phone;
                }

                if ($request->filled('address') && $request->address !== $existingClient->address) {
                    $updateData['address'] = $request->address;
                }

                if ($request->filled('city') && $request->city !== $existingClient->city) {
                    $updateData['city'] = $request->city;
                }

                if (!empty($updateData)) {
                    $existingClient->update($updateData);
                }

                return $this->sendResponse([
                    'client' => [
                        'id' => $existingClient->id,
                        'name' => $existingClient->name,
                        'email' => $existingClient->email,
                        'phone' => $existingClient->phone,
                        'address' => $existingClient->address,
                        'city' => $existingClient->city,
                    ],
                    'is_existing' => true
                ], 'Client information updated successfully');
            }

            // Create new client
            $client = Client::create($request->only([
                'name',
                'email',
                'phone',
                'address',
                'city'
            ]));

            return $this->sendResponse([
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                    'city' => $client->city,
                ],
                'is_existing' => false
            ], 'Client registered successfully', 201);
        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Registration failed', ['error' => 'Unable to register client'], 500);
        }
    }

    /**
     * Get client by email for order tracking.
     */
    public function getByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $client = Client::where('email', $request->email)->first();

        if (!$client) {
            return $this->sendError('Client not found', ['email' => 'No client found with this email address'], 404);
        }

        return $this->sendResponse([
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
            ]
        ], 'Client found successfully');
    }
}
