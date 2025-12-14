<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Select\SelectService;
use Illuminate\Http\Request;

class SelectController extends Controller
{
    private $selectService;

    public function __construct(SelectService $selectService)
    {
        $this->selectService = $selectService;
    }

    public function getSelects(Request $request)
    {
        $selectData = $this->selectService->getSelects($request->allSelects);

        return ApiResponse::success($selectData);
    }


}
