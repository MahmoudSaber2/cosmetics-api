<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;
use App\Helpers\ApiResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;

class LogoutController extends Controller implements HasMiddleware{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    public function __invoke()
    {
        $user = auth()->user();

        if ($user) {
            //$user->tokens()->delete(); // Revoke all tokens
            $user->currentAccessToken()->delete();
        }
        return ApiResponse::success([], __('messages.logged_out'));
    }


}
