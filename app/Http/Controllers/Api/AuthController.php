<?php

namespace App\Http\Controllers\Api;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
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
                'email' => [__('auth.failed')],
            ]);
        }

        // Check if user has admin access (admin or manager role)
        if (!$user->hasAdminAccess()) {
            throw ValidationException::withMessages([
                'email' => [__('messages.forbidden')],
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
        ], 'messages.success');
    }

    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse([], 'messages.success');
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return $this->sendResponse($request->user(), 'messages.retrieved');
    }
}
