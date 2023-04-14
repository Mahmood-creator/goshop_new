<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Repositories\Interfaces\RevenueReportRepoInterface;
use Illuminate\Http\Request;

class RevenueController extends AdminBaseController
{
    private RevenueReportRepoInterface $repository;


    public function __construct(
        RevenueReportRepoInterface $repository
    ) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function reportChart()
    {
        try {
            $result = $this->repository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->repository->reportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
