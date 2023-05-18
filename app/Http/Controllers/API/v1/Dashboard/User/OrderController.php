<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Traits\Notification;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\OrderResource;
use App\Http\Requests\FilterParamsRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\User\Order\StoreRequest;
use App\Services\OrderService\OrderReviewService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\OrderService\OrderStatusUpdateService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends UserBaseController
{
    use Notification;

    private OrderRepoInterface $orderRepository;
    private OrderServiceInterface $orderService;

    /**
     * @param OrderRepoInterface $orderRepository
     * @param OrderServiceInterface $orderService
     */
    public function __construct(OrderRepoInterface $orderRepository, OrderServiceInterface $orderService)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
    }


    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15,
            auth('sanctum')->id(), $request->all());
        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $result = $this->orderService->create($request->merge(['user_id' => auth('sanctum')->id()]));
        if ($result['status']) {
            // Select Admin Firebase Token to Push Notification
            $admins = User::whereHas('roles', function ($q) {
                $q->whereIn('role_id', [99, 21]);
            })->whereNotNull('firebase_token')->pluck('firebase_token');
            // Select Seller Firebase Token to Push Notification
            $sellers = User::whereHas('shop', function ($q) use ($result) {
                $q->whereIn('id', $result['data']->orderDetails()->pluck('shop_id'));
            })->whereNotNull('firebase_token')->pluck('firebase_token');
            // Send notification about new Order.
            $this->sendNotification(array_merge($admins->toArray(), $sellers->toArray()), "New order was created", $result['data']->id);
            return $this->successResponse(__('web.record_was_successfully_create'), $result['data']);
        }

        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->orderDetails($id);
        if ($order && $order->user_id == auth('sanctum')->id()) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function addOrderReview(int $id, Request $request): JsonResponse
    {
        $result = (new OrderReviewService())->addReview($id, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }


    public function orderStatusChange(Request $request, int $id): JsonResponse|AnonymousResourceCollection
    {
        $order = Order::find($id);

        if ($order->status == $request->status) {
            return $this->errorResponse(ResponseError::ERROR_252,
                trans('errors.' . ResponseError::ERROR_252, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        } elseif ($order->status == Order::CANCELED) {
            return $this->errorResponse(ResponseError::ERROR_254,
                trans('errors.' . ResponseError::ERROR_254, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->update(['status' => $request->status]);


        foreach ($order->orderDetails as $detail) {
            $this->orderDetailStatusChange($detail->id, $request);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }
    /**
     * Add Review to OrderDetails.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDetailStatusChange(int $id, Request $request): JsonResponse
    {
        if (!isset($request->status) || $request->status != 13) {
            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }
        $detail = OrderDetail::find($id);

        if ($detail) {
            $result = (new OrderStatusUpdateService())->statusUpdate($detail, $request->status);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($result['data']));
            }
            return $this->errorResponse(
                $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
                Response::HTTP_BAD_REQUEST
            );
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }


}
