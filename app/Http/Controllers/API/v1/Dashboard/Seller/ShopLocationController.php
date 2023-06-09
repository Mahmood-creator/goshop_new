<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ShopLocationResource;
use App\Http\Requests\Seller\DeleteAllRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Seller\ShopLocation\IndexRequest;
use App\Http\Requests\Seller\ShopLocation\StoreRequest;
use App\Http\Requests\Seller\ShopLocation\UpdateRequest;
use App\Services\ShopLocationService\ShopLocationService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Repositories\ShopLocationRepository\ShopLocationRepository;

class ShopLocationController extends SellerBaseController
{
    public function __construct(
        protected ShopLocationService    $service,
        protected ShopLocationRepository $repository
    )
    {
        parent::__construct();
    }

    /**
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $products = $this->repository->paginate($collection);

        return ShopLocationResource::collection($products);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->repository->show($id,$this->shop->id);

        if ($result) {
            return $this->successResponse(__('web.payment_found'), ShopLocationResource::make($result));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->service->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), ShopLocationResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Store a newly created resource in storage.
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $collection = $request->validated();

        $collection['shop_id'] = $this->shop->id;

        $result = $this->service->update($collection, $id);

        if ($result['status']) {
            return $this->successResponse(__('web.record_was_successfully_create'), ShopLocationResource::make($result['data']));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteAllRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function destroy(DeleteAllRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $result = $this->service->destroy($collection['ids']);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }
}
