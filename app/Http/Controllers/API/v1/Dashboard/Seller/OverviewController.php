<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\OverviewLeaderboardsReportRequest;
use App\Http\Requests\OverviewReportChartRequest;
use App\Repositories\Interfaces\OverviewReportRepoInterface;
use Illuminate\Http\Request;

class OverviewController extends SellerBaseController
{
    private OverviewReportRepoInterface $repository;


    public function __construct(
        OverviewReportRepoInterface $repository
    ) {
        parent::__construct();
        $this->repository = $repository;
    }

    public function reportChart(OverviewReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->repository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }


    public function leaderboards(int $limit, OverviewLeaderboardsReportRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->repository->leaderboards($limit);

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
