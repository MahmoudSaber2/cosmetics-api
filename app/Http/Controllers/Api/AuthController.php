<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Login API
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user has admin access (admin or manager role)
        if (!$user->hasAdminAccess()) {
            throw ValidationException::withMessages([
                'email' => ['Access denied. Admin privileges required.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->sendResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 'User logged in successfully');
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'User logged out successfully');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return $this->sendResponse($request->user(), 'User retrieved successfully');
    }
}
