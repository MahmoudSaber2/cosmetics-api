<?php

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Requests\V1\Auth\LoginUserRequest;
use App\Http\Resources\V1\User\UserProfileResource;
use App\Models\User;
use App\Services\UserRolePremission\UserPermissionService;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    public function __construct(private UserPermissionService $userPermissionService)
    {
    }
    /**
     * Login API
     */
    public function __invoke(LoginUserRequest $request)
    {
        $request->validated();

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('', __('messages.invalid_credentials'), HttpStatusCode::UNAUTHORIZED);
        }

        if(!$user->isActive()){
            return ApiResponse::error(__('messages.in_active_user'), [], HttpStatusCode::UNPROCESSABLE_ENTITY);
        }



        // Check if user has admin access (admin or manager role)
        if (!$user->hasAdminAccess()) {
            return ApiResponse::error('', __('messages.unauthorized'), HttpStatusCode::UNAUTHORIZED);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        $expiration = config('sanctum.expiration'); // بالدقايق

        return ApiResponse::success([
            'profile' => new UserProfileResource($user),
            'tokenDetails' => [
                'accessToken' => $token,
                'expiresIn' => $expiration??"",
            ],
            'role' => $user->getRoleNames()->first()->name ?? '',
            'permissions' => $this->userPermissionService->getUserPermissions($user) ?? [],
        ]);
    }
}
