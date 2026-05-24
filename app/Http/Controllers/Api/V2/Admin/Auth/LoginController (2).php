<?php

namespace App\Http\Controllers\Api\V2\Admin\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Requests\V2\Auth\LoginUserRequest;
use App\Http\Resources\V2\User\UserProfileResource;
use App\Models\User;
use App\Services\UserRolePremission\UserPermissionService;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Admin Authentication",
 *     description="Authentication operations for admin users"
 * )
 */
class LoginController extends Controller
{
    public function __construct(private UserPermissionService $userPermissionService)
    {
    }
    /**
     * @OA\Post(
     *     path="/api/v1/admin/auth/login",
     *     summary="Admin user login",
     *     description="Authenticate admin user and return access token with user profile and permissions",
     *     operationId="adminLogin",
     *     tags={"Admin Authentication"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", description="User email address", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", description="User password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="profile",
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="أحمد محمد"),
     *                     @OA\Property(property="email", type="string", example="admin@example.com")
     *                 ),
     *                 @OA\Property(
     *                     property="tokenDetails",
     *                     type="object",
     *                     @OA\Property(property="accessToken", type="string", example="1|abcdef123456789"),
     *                     @OA\Property(property="expiresIn", type="string", example="1440")
     *                 ),
     *                 @OA\Property(property="role", type="string", example="admin"),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(
                         @OA\Property(property="permissionName", type="string", example="view_users"),
                         @OA\Property(property="access", type="boolean", example=true)
                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="بيانات الدخول غير صحيحة"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error or Inactive User",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="المستخدم غير نشط"),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
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
