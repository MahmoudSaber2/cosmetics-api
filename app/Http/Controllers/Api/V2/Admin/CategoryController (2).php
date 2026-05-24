<?php

namespace App\Http\Controllers\Api\V2\Admin;

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
use App\Http\Requests\V2\Category\StoreCategoryRequest;
use App\Http\Requests\V2\Category\UpdateCategoryRequest;
use App\Http\Resources\V2\Category\CategoryCollection;
use App\Http\Resources\V2\Category\CategoryResource;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class CategoryController extends Controller implements HasMiddleware
{
    public function __construct()
    {
    }

    public static function middleware(): array
    {
        return [
            // new Middleware('auth:sanctum'),
            // new Middleware('permission:all_categories', only:['index']),
            // new Middleware('permission:create_category', only:['store']),
            // new Middleware('permission:edit_category', only:['show']),
            // new Middleware('permission:update_category', only:['update']),
            // new Middleware('permission:delete_category', only:['destroy']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/v2/admin/categories",
     *     operationId="getAllCategoriesV2",
     *     summary="Get all categories (V2)",
     *     description="Retrieve a paginated list of categories with optional search and status filters, including images.",
     *     tags={"Categories V2"},
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
     *         description="Search by category name or description",
     *         required=false,
     *         @OA\Schema(type="string", example="Electronics")
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filter categories by status (0 => inactive, 1 => active)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"0", "1"}, example="1")
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
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Categories retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="categoryId", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Electronics"),
     *                         @OA\Property(property="slug", type="string", example="electronics"),
     *                         @OA\Property(property="status", type="integer", example=1),
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=25),
     *                     @OA\Property(property="count", type="integer", example=15),
     *                     @OA\Property(property="perPage", type="integer", example=15),
     *                     @OA\Property(property="currentPage", type="integer", example=1),
     *                     @OA\Property(property="totalPages", type="integer", example=2)
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
     *         description="Forbidden - Insufficient permissions to view categories",
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
     * @OA\Post(
     *     path="/api/v2/admin/categories",
     *     operationId="storeCategoryV2",
     *     summary="Create a new category (V2)",
     *     description="Creates a new category with name, slug, description, status, and optional image.",
     *     tags={"Categories V2"},
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
     *                 required={"name", "slug", "status"},
     *                 @OA\Property(property="name", type="string", example="Electronics", description="Category name (must be unique)"),
     *                 @OA\Property(property="slug", type="string", example="electronics", description="Category slug (must be unique)"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Electronic devices and accessories"),
     *                 @OA\Property(property="status", type="integer", enum={"0","1"}, example=1, description="Category status (0 => inactive, 1 => active)"),
     *                 @OA\Property(property="image", type="string", format="binary", nullable=true, description="Category image (optional, max 2MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category created successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name field is required.")
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
     *                     type="array",
     *                     @OA\Items(type="string", example="The slug has already been taken.")
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
     *         description="Forbidden - Insufficient permissions to create categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while creating the category."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        Category::create($data);

        return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/admin/categories/{id}",
     *     operationId="showCategoryV2",
     *     summary="Get category details (V2)",
     *     description="Retrieve detailed information about a specific category by ID, including image.",
     *     tags={"Categories V2"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
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
     *         description="Category details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="categoryId", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Electronics"),
     *                 @OA\Property(property="slug", type="string", example="electronics"),
     *                 @OA\Property(property="description", type="string", example="Electronic devices and accessories"),
     *                 @OA\Property(property="status", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found."),
     *             @OA\Property(property="data", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to view category details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function show(Category $category)
    {
        return ApiResponse::success(new CategoryResource($category));
    }

    /**
     * @OA\Post(
     *     path="/api/v2/admin/categories/{id}",
     *     operationId="updateCategoryV2",
     *     summary="Update category information (V2)",
     *     description="Updates an existing category's information including name, slug, description, status, and optional image.",
     *     tags={"Categories V2"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID to update",
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
     *     @OA\Parameter(
     *         name="_method",
     *         in="query",
     *         required=true,
     *         description="HTTP method override for form data",
     *         @OA\Schema(type="string", enum={"PUT"}, example="PUT")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "slug", "status"},
     *                 @OA\Property(property="name", type="string", example="Electronics Updated", description="Category name (must be unique)"),
     *                 @OA\Property(property="slug", type="string", example="electronics-updated", description="Category slug (must be unique)"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Updated electronic devices and accessories"),
     *                 @OA\Property(property="status", type="integer", enum={"0","1"}, example=1, description="Category status (0 => inactive, 1 => active)"),
     *                 @OA\Property(property="image", type="string", format="binary", nullable=true, description="Category image (optional, max 2MB)")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category updated successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="array",
     *                     @OA\Items(type="string", example="The name has already been taken.")
     *                 ),
     *                 @OA\Property(
     *                     property="slug",
     *                     type="array",
     *                     @OA\Items(type="string", example="The slug has already been taken.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found."),
     *             @OA\Property(property="data", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to update categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error - Database error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while updating the category."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        $category->update($data);

        return ApiResponse::success([], __('messages.updated'));
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/admin/categories/{id}",
     *     operationId="deleteCategoryV2",
     *     summary="Delete a category (V2)",
     *     description="Permanently delete a category from the system, including its image.",
     *     tags={"Categories V2"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID to delete",
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
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found."),
     *             @OA\Property(property="data", type="object", example={})
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
     *         description="Forbidden - Insufficient permissions to delete categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This action is unauthorized."),
     *             @OA\Property(property="data", type="object", example={})
     *         )
     *     )
     * )
     */
    public function destroy(Category $category)
    {
        // Delete image if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }

}
