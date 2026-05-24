<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Filters\User\FilterUser;
use App\Filters\User\FilterUserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\StoreUserRequest;
use App\Http\Requests\V2\User\UpdateUserRequest;
use App\Http\Resources\V2\User\UserCollection;
use App\Http\Resources\V2\User\UserResource;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use OpenApi\Attributes as OA;


class UserController extends Controller implements HasMiddleware
{

    public function __construct(){}
    /**
     * Display a listing of users.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('permission:all_users', only:['index']),
            new Middleware('permission:create_user', only:['store']),
            new Middleware('permission:edit_user', only:['show']),
            new Middleware('permission:update_user', only:['update']),
            new Middleware('permission:delete_user', only:['destroy']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     operationId="getAllUsers",
     *     summary="Get all users",
     *     description="Retrieve a paginated list of users with optional filters for search, status, and role.",
     *     tags={"Users"},
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
     *         description="Search by name, email or phone",
     *         required=false,
     *         @OA\Schema(type="string", example="john")
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter users by status 0 => inactive or 1 => active",
     *         required=false,
     *         @OA\Schema(type="string", enum={"0", "1"}, example="1")
     *     ),
     *     @OA\Parameter(
     *         name="filter[role]",
     *         in="query",
     *         description="Filter users by role Id 1, 2 ..etc",
     *         required=false,
     *         @OA\Schema(type="string", example="2")
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
     *         description="",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Users retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="users",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="userId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", example="john@example.com"),
     *                         @OA\Property(property="phone", type="string", example="1234567890"),
     *                         @OA\Property(property="status", type="integer", example=1),
     *                         @OA\Property(property="roleName", type="integer", example="Admin"),
     *                         @OA\Property(property="createdAt", type="string", example="2023-01-01T00:00:00Z")
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
     *         description="Forbidden - Insufficient permissions to view users",
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

    public function index()
    {
        $users = QueryBuilder::for(User::class)
            ->allowedFilters(
                AllowedFilter::custom('search', new FilterUser()), // Add a custom search filter
                AllowedFilter::exact('status', 'status'),
                AllowedFilter::custom('role', new FilterUserRole()))
            ->select('id', 'name', 'email', 'phone', 'status', 'created_at')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->whereNot('id', auth('sanctum')->id())
            ->paginate(request()->get('perPage', 15));

        return ApiResponse::success(new UserCollection($users));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users",
     *     operationId="storeUser",
     *     summary="Create a new user",
     *     description="Creates a new user with role, status, and optional avatar image.",
     *     tags={"Users"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","status","password","roleId"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="1234567890"),
     *                 @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="status", type="integer", enum={"0","1"}, example="0", description="User status (0 => inactive, 1 => active)"),
     *                 @OA\Property(property="password", type="string", format="password", example="Password123"),
     *                 @OA\Property(property="roleId", type="integer", example=2),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User created successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to create users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database transaction failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while creating the user."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $request->validated();

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'status' => StatusEnum::from($request->status),
                'password' => $request->password,
            ]);

            $role = Role::find($request->roleId);
            $user->assignRole($role);

            DB::commit();


            return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('messages.error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
            //throw $th;
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/{id}",
     *     operationId="showUser",
     *     summary="Get user details",
     *     description="Retrieve detailed information about a specific user by ID.",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
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
     *         description="User details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="userId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="phone", type="string", example="1234567890"),
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="status", type="integer", example=1),
     *                 @OA\Property(property="roleId", type="integer", example=2)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *             @OA\Property(property="errors", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to view user details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function show(User $user)
    {
        return ApiResponse::success(new UserResource($user));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/users/{id}",
     *     operationId="updateUser",
     *     summary="Update user information",
     *     description="Updates an existing user's information including role, status, and optional avatar image.",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID to update",
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
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","status","roleId"},
     *                 @OA\Property(property="name", type="string", example="John Doe Updated"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.updated@example.com"),
     *                 @OA\Property(property="phone", type="string", nullable=true, example="0987654321"),
     *                 @OA\Property(property="address", type="string", nullable=true, example="456 Updated St"),
     *                 @OA\Property(property="status", type="integer", enum={"0","1"}, example="1", description="User status (0 => inactive, 1 => active)"),
     *                 @OA\Property(property="password", type="string", format="password", nullable=true, example="NewPassword123", description="Leave empty to keep current password"),
     *                 @OA\Property(property="roleId", type="integer", example=3),
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *             @OA\Property(property="errors", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to update users",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database transaction failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the user."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            //code...

            $request->validated();

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'phone' => $request->phone,
                'status' => StatusEnum::from($request->status),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
            }

            DB::beginTransaction();

            $user->update($updateData);

            $role = Role::find($request->roleId);
            $user->syncRoles($role);

            DB::commit();

            return ApiResponse::success([], __('messages.updated'));

        } catch (\Throwable $th) {
            DB::rollBack();
            return ApiResponse::error(__('messages.error'), [], HttpStatusCode::INTERNAL_SERVER_ERROR);
            //throw $th;
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/users/{id}",
     *     operationId="deleteUser",
     *     summary="Delete a user",
     *     description="Permanently delete a user from the system. Users cannot delete themselves.",
     *     tags={"Users"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID to delete",
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
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User deleted successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Cannot delete yourself or insufficient permissions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="You cannot delete yourself."),
     *             @OA\Property(property="errors", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found."),
     *             @OA\Property(property="errors", type="object", example={})
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
     * )
     */
    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth('sanctum')->id()) {
            return ApiResponse::error(__('messages.forbidden'), [], HttpStatusCode::FORBIDDEN);
        }

        $user->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }
}
