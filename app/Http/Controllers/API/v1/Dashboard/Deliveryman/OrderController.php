<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Helpers\ResponseError;
use App\Http\Requests\DeliveryMan\Order\ReportRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Repositories\OrderRepository\OrderDetailRepository;
use App\Repositories\OrderRepository\OrderRepository;
use App\Services\OrderService\OrderDetailService;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends DeliverymanBaseController
{
    use Notification;
    private Order $model;

    public function __construct(private OrderDetailRepository $orderRepository, Order $model,OrderDetailService $service)
    {
        parent::__construct();
        $this->model = $model;
        $this->service = $service;
        $this->lang = \request('lang') ?? 'aze';
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = auth('sanctum')->user();
        $filter = $request->all();
        $filter['deliveryman'] = auth('sanctum')->id();

        unset($filter['isset-deliveryman']);

        if (data_get($filter, 'empty-deliveryman')) {
            $filter['shop_ids'] = $user->invitations->pluck('shop_id')->toArray();
            unset($filter['deliveryman']);
        }
        $orderDetails = $this->orderRepository->paginate(array: $filter);

        return OrderDetailResource::collection($orderDetails);
    }

    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $orderDetail = $this->orderRepository->getById($id);
        if ($orderDetail){
            return $this->successResponse(__('web.order_found'), OrderDetailResource::make($orderDetail));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param FilterParamsRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function statusChange(Request $request, int $id): JsonResponse
    {
        $order = OrderDetail::find($id);

        if ($order->status == $request->status) {
            return $this->errorResponse(ResponseError::ERROR_252,
                trans('errors.' . ResponseError::ERROR_252, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        } elseif ($order->status == OrderDetail::CANCELED) {
            return $this->errorResponse(ResponseError::ERROR_254,
                trans('errors.' . ResponseError::ERROR_254, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }elseif (!in_array($request->status,[OrderDetail::DELIVERED,OrderDetail::CANCELED])){
            return $this->errorResponse(ResponseError::ERROR_253,
                trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->update(['status' => $request->status]);

        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Display the specified resource.
     *
     * @param int|null $id
     * @return JsonResponse
     */
    public function orderDeliverymanUpdate(?int $id): JsonResponse
    {
        $result = $this->service->attachDeliveryMan($id);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(__('web.delivery_man_setting_found'), OrderDetailResource::make(data_get($result, 'data'))
        );
    }

    public function report(ReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['deliveryman'] = auth('sanctum')->id();

        return $this->successResponse(
            __('web.report_found'),
            $this->orderRepository->deliveryManReport($validated)
        );
    }
}
