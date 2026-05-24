<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;
use App\Helpers\ApiResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

class LogoutController extends Controller implements HasMiddleware{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
        ];
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/auth/logout",
     *     summary="Admin user logout",
     *     description="Logout admin user and revoke current access token",
     *     operationId="adminLogout",
     *     tags={"Admin Authentication"},
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
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم تسجيل الخروج بنجاح"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or expired token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated"),
     *             @OA\Property(property="error", type="object", example={})
     *         )
     *     )
     * )
     */
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
