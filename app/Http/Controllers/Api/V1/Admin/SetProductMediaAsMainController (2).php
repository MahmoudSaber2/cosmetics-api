<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\IsMainEnum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductMedia\ProductMediaResource;
use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class SetProductMediaAsMainController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum'),
            // new Middleware('permission:edit_products'),
        ];
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/products/{product}/media/{media}/set-main",
     *     summary="Set media as main",
     *     description="Set a specific media file as the main media for a product",
     *     operationId="setProductMediaAsMain",
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
     *         description="Media set as main successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="تم تعيين الملف كصورة رئيسية"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="mediaId", type="integer", example=1),
     *                 @OA\Property(property="url", type="string", example="products/image1.jpg"),
     *                 @OA\Property(property="isMain", type="boolean", example=true)
     *             )
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
    public function __invoke(Product $product, ProductMedia $media)
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

            DB::beginTransaction();

            // Remove main status from all product media
            $product->media()->update(['is_main' => IsMainEnum::NOT_MAIN->value]);

            // Set this media as main
            $media->update(['is_main' => IsMainEnum::MAIN->value]);

            DB::commit();

            return ApiResponse::success(
                new ProductMediaResource($media->fresh()),
                __('messages.media_set_as_main')
            );

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
