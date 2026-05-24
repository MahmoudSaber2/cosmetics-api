<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Select\SelectService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Select Options",
 *     description="Dynamic select options for forms and dropdowns"
 * )
 */
class SelectController extends Controller
{
    private $selectService;

    public function __construct(SelectService $selectService)
    {
        $this->selectService = $selectService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/selects",
     *     summary="Get dynamic select options",
     *     description="Retrieve dynamic select options for forms and dropdowns based on requested selects",
     *     operationId="getSelectOptions",
     *     tags={"Select Options"},
     *     @OA\Parameter(
     *         name="Accept-Language",
     *         in="header",
     *         description="Language preference (ar, en)",
     *         required=false,
     *         @OA\Schema(type="string", example="ar")
     *     ),
     *     @OA\Parameter(
     *         name="allSelects",
     *         in="query",
     *         description="Comma-separated list of select types to retrieve",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="users,outerCategories",
     *             enum={"users", "outerCategories"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Select options retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="label", type="string", example="users"),
     *                     @OA\Property(
     *                         property="options",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="value", type="integer", example=1),
     *                             @OA\Property(property="label", type="string", example="أحمد محمد")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request - Invalid select type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid select type"),
     *             @OA\Property(property="error", type="object", example={})
     *         )
     *     )
     * )
     */
    public function getSelects(Request $request)
    {
        $selectData = $this->selectService->getSelects($request->allSelects);

        return ApiResponse::success($selectData);
    }


}
