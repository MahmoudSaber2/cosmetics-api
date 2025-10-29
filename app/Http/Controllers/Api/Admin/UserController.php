<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseApiController
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($users, 'Users retrieved successfully');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'manager'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 'User created successfully', 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 'User retrieved successfully');
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['admin', 'manager'])],
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 'User updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return $this->sendError('Cannot delete your own account', [], 400);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully');
    }
}
