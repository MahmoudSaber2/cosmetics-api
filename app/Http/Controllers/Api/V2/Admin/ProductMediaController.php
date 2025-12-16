<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Enums\IsMainEnum;
use App\Enums\MediaTypeEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ProductMedia\StoreProductMediaRequest;
use App\Http\Resources\V1\ProductMedia\ProductMediaCollection;
use App\Http\Resources\V1\ProductMedia\ProductMediaResource;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Product Media",
 *     description="Product media management operations"
 * )
 */
class ProductMediaController extends Controller implements HasMiddleware
{
    public function __construct(private FileUploadService $fileUploadService)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            // new Middleware('permission:view_products', only: ['index', 'show']),
            // new Middleware('permission:create_products', only: ['store']),
            // new Middleware('permission:edit_products', only: ['update', 'setAsMain']),
            // new Middleware('permission:delete_products', only: ['destroy']),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/products/{product}/media",
     *     summary="Get product media list",
     *     description="Retrieve all media files for a specific product",
     *     operationId="getProductMedia",
     *     tags={"Product Media"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product media retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="mediaId", type="integer", example=1),
     *                     @OA\Property(property="url", type="string", example="products/image1.jpg"),
     *                     @OA\Property(property="fullUrl", type="string", example="https://example.com/storage/products/image1.jpg"),
     *                     @OA\Property(property="mediaType", type="string", example="image"),
     *                     @OA\Property(property="isMain", type="boolean", example=true),
     *                     @OA\Property(property="createdAt", type="string", example="01/12/24 02:30 م")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function index(Product $product)
    {
        $media = $product->media()->orderBy('is_main', 'desc')->orderBy('created_at', 'asc')->get();

        return ApiResponse::success(new ProductMediaCollection($media));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/products/{product}/media",
     *     summary="Upload single product media",
     *     description="Upload a single media file for a product",
     *     operationId="storeProductMedia",
     *     tags={"Product Media"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="media",
     *                     type="string",
     *                     format="binary",
     *                     description="Media file (image/video) - max 5MB"
     *                 ),
     *                 @OA\Property(
     *                     property="isMain",
     *                     type="integer",
     *                     nullable=true,
     *                     description="Set this media as main media for the product (0=not main, 1=main)",
     *                     example=1
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Media uploaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم رفع الملف بنجاح"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="mediaId", type="integer", example=1),
     *                 @OA\Property(property="url", type="string", example="products/image1.jpg"),
     *                 @OA\Property(property="fullUrl", type="string", example="https://example.com/storage/products/image1.jpg"),
     *                 @OA\Property(property="mediaType", type="string", example="image"),
     *                 @OA\Property(property="mediaTypeLabel", type="string", example="صورة"),
     *                 @OA\Property(property="isMain", type="boolean", example=true),
     *                 @OA\Property(property="createdAt", type="string", example="01/12/24 02:30 م")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="media",
     *                     type="array",
     *                     @OA\Items(type="string", example="يجب أن يكون الملف صورة أو فيديو")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function store(StoreProductMediaRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $file = $request->file('media');

            // Determine media type
            $mediaType = $this->fileUploadService->getMediaType($file);

            // Upload file
            $uploadData = $this->fileUploadService->uploadImage($file, 'products');

            // Check if this should be set as main
            $isMainValue = IsMainEnum::NOT_MAIN->value;
            if ($request->has('isMain') && $request->isMain === IsMainEnum::MAIN->value) {
                // Remove main status from other media
                $product->media()->update(['is_main' => IsMainEnum::NOT_MAIN->value]);
                $isMainValue = IsMainEnum::MAIN->value;
            } elseif ($product->media()->count() === 0) {
                // Set as main if no media exists
                $isMainValue = IsMainEnum::MAIN->value;
            }

            // Create media record
            $media = ProductMedia::create([
                'product_id' => $product->id,
                'url' => $uploadData['path'],
                'media_type' => $mediaType,
                'is_main' => $isMainValue,
            ]);

            DB::commit();

            return ApiResponse::success(
                new ProductMediaResource($media),
                __('messages.media_uploaded_successfully'),
                HttpStatusCode::CREATED
            );

        } catch (\Exception $e) {
            DB::rollback();

            // Clean up uploaded file on error
            if (isset($uploadData)) {
                $this->fileUploadService->deleteImage($uploadData['path']);
            }

            return ApiResponse::error(
                __('messages.error'),
                ['error' => $e->getMessage()],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/products/{product}/media/{media}",
     *     summary="Delete product media",
     *     description="Delete a specific media file from a product",
     *     operationId="deleteProductMedia",
     *     tags={"Product Media"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="media",
     *         in="path",
     *         description="Media ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Media deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم حذف الملف بنجاح"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cannot delete main media when other media exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="لا يمكن حذف الصورة الرئيسية عندما توجد صور أخرى")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Media not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Media not found")
     *         )
     *     )
     * )
     */
    public function destroy(Product $product, ProductMedia $media)
    {
        try {
            // Check if media belongs to product
            if ($media->product_id !== $product->id) {
                return ApiResponse::error(
                    __('messages.media_not_found'),
                    [],
                    HttpStatusCode::NOT_FOUND
                );
            }

            // Check if trying to delete main media when other media exists
            if ($media->is_main && $product->media()->count() > 1) {
                return ApiResponse::error(
                    __('messages.cannot_delete_main_media'),
                    [],
                    HttpStatusCode::BAD_REQUEST
                );
            }

            DB::beginTransaction();

            // Delete file from storage
            $this->fileUploadService->deleteImage($media->getRawOriginal('url'));

            // Delete media record
            $media->delete();

            // If this was the main media and other media exists, set first one as main
            if ($media->is_main && $product->media()->count() > 0) {
                $product->media()->first()->update(['is_main' => IsMainEnum::MAIN->value]);
            }

            DB::commit();

            return ApiResponse::success([], __('messages.media_deleted_successfully'));

        } catch (\Exception $e) {
            DB::rollback();

            return ApiResponse::error(
                __('messages.error'),
                ['error' => $e->getMessage()],
                HttpStatusCode::INTERNAL_SERVER_ERROR
            );
        }
    }


}
