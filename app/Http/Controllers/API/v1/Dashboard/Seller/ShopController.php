<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportCompareRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepoInterface;
use App\Services\Interfaces\ShopServiceInterface;
use App\Services\ShopServices\ShopActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShopController extends SellerBaseController
{
    private ShopRepoInterface $shopRepository;
    private ShopServiceInterface $shopService;

    public function __construct(ShopRepoInterface $shopRepository, ShopServiceInterface $shopService)
    {
        parent::__construct();
        $this->shopRepository = $shopRepository;
        $this->shopService = $shopService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse|
     */
    public function shopCreate(Request $request)
    {
        $result = $this->shopService->create($request);
        if ($result['status']) {
            auth('sanctum')->user()->invitations()->delete();
            return $this->successResponse(__('web.record_successfully_created'), ShopResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function shopShow()
    {
        $shop = $this->shopRepository->show($this->shop->uuid);
        if ($shop) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), ShopResource::make($shop->load('translations', 'seller.wallet')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shopUpdate(Request $request)
    {
        $result = $this->shopService->update($this->shop->uuid, $request);
        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function setVisibilityStatus()
    {
        (new ShopActivityService())->changeVisibility($this->shop->uuid);
        return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($this->shop));
    }

    public function setWorkingStatus()
    {
        (new ShopActivityService())->changeOpenStatus($this->shop->uuid);
        $shop = Shop::find($this->shop->id);
        return $this->successResponse(__('web.record_successfully_updated'), ShopResource::make($shop));
    }


    public function getWithSeller(FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->getShopWithSellerCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportPaginate(ReportPaginateRequest $request, FilterParamsRequest $filterParamsRequest)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportPaginateCache($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportChart(ReportChartRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function reportCompare(ReportCompareRequest $request)
    {
        try {
            request()->offsetSet('sellers', [auth('sanctum')->id()]);
            request()->offsetSet('shops', [auth('sanctum')->user()->shop->id]);
            $result = $this->shopRepository->reportCompareCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

}
