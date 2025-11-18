<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\StatusEnum;
use App\Filters\User\FilterUser;
use App\Filters\User\FilterUserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\User\StoreUserRequest;
use App\Http\Requests\V1\User\UpdateUserRequest;
use App\Http\Resources\V1\User\UserCollection;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
            ->whereNot('id', auth()->id())
            ->paginate(request()->get('perPage', 15));

        return ApiResponse::success(new UserCollection($users));
    }

    /**
     * Store a newly created user.
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
     * Display the specified user.
     */
    public function show(User $user)
    {
        return ApiResponse::success(new UserResource($user));
    }

    /**
     * Update the specified user.
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
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return ApiResponse::error(__('messages.forbidden'), [], HttpStatusCode::FORBIDDEN);
        }

        $user->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }
}
