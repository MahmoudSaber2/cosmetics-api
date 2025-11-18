<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Filters\Brand\FilterBrand;
use App\Helpers\ApiResponse;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Brand\StoreBrandRequest;
use App\Http\Requests\V1\Brand\UpdateBrandRequest;
use App\Http\Resources\V1\Brand\BrandCollection;
use App\Http\Resources\V1\Brand\BrandResource;

class BrandController extends Controller implements HasMiddleware
{
    public function __construct()
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            new Middleware('permission:all_brands', only:['index']),
            new Middleware('permission:create_brand', only:['store']),
            new Middleware('permission:edit_brand', only:['show']),
            new Middleware('permission:update_brand', only:['update']),
            new Middleware('permission:delete_brand', only:['destroy']),
        ];
    }

    /**
     * Display a listing of clients with optional search and filtering.
     */
    public function index(Request $request)
    {

        $brands = QueryBuilder::for(Brand::class)
            ->allowedFilters([
                AllowedFilter::custom('search', new FilterBrand()),
            ])
            ->defaultSort('-created_at')
            ->paginate($request->get('perPage', 15));

        return ApiResponse::success(new BrandCollection($brands));

    }

    /**
     * Store a newly created client.
     */
    public function store(StoreBrandRequest $request)
    {
        $request->validated();

        $brand = Brand::create([
            'name' => $request->name,
        ]);

        return ApiResponse::success([], __('messages.created'), HttpStatusCode::CREATED);
    }

    /**
     * Display the specified client.
     */
    public function show(Brand $brand)
    {
        return ApiResponse::success(new BrandResource($brand));
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();
        $brand->update($data);

        return ApiResponse::success([], __('messages.updated'));
    }

    /**
     * Remove the specified client.
     */
    public function destroy(Brand $brand)
    {

        $brand->delete();

        return ApiResponse::success([], __('messages.deleted'));
    }

}
