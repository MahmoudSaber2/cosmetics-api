<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Category\FilterCategory;
use App\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Category\StoreCategoryRequest;
use App\Http\Requests\V1\Category\UpdateCategoryRequest;
use App\Http\Resources\V1\Category\CategoryCollection;
use App\Http\Resources\V1\Category\CategoryResource;

class CategoryController extends Controller implements HasMiddleware
{
    public function __construct()
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('permission:all_categories', only:['index']),
            new Middleware('permission:create_category', only:['store']),
            new Middleware('permission:edit_category', only:['show']),
            new Middleware('permission:update_category', only:['update']),
            new Middleware('permission:delete_category', only:['destroy']),
        ];
    }

    /**
     * Display a listing of clients with optional search and filtering.
     */
    public function index(Request $request)
    {

        $categories = QueryBuilder::for(Category::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterCategory()),
                AllowedFilter::exact('status', 'status')
            ])
            ->defaultSort('-created_at')
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new CategoryCollection($categories));

    }

    /**
     * Store a newly created client.
     */
    public function store(StoreCategoryRequest $request)
    {
        $request->validated();

        Category::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description??'',
            'status' => $request->status,
        ]);

        return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED);
    }

    /**
     * Display the specified client.
     */
    public function show(Category $category)
    {
        return ApiResponse::success(new CategoryResource($category));
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $category->update($data);

        return ApiResponse::success([], __('messages.updated'));
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Category $category)
    {

        $category->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }

}
