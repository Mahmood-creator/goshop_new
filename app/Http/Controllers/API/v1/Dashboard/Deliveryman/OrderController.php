<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository\OrderRepository;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends DeliverymanBaseController
{
    use Notification;
    private OrderRepository $orderRepository;
    private Order $model;

    public function __construct(OrderRepository $orderRepository, Order $model)
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->model = $model;
        $this->lang = \request('lang') ?? 'aze';
    }

    public function paginate(Request $request): AnonymousResourceCollection
    {
        $orders = $this->model
            ->with([
                'orderDetails' => fn($q) => $q->where('deliveryman', auth('sanctum')->id()),
                'user',
                'transaction.paymentSystem.translation' => fn($q) => $q->actualTranslation($request->lang ?? 'en'),
                'orderDetails.orderStocks.stock.discount',
                'orderDetails.deliveryAddress',
                'orderDetails.shop.translation' => fn($q) => $q->actualTranslation($request->lang ?? 'en'),
                'orderDetails.orderStocks.stock' => function ($q) {
                    $q->select('id', 'countable_id', 'countable_type');
                },
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                }])
            ->whereIn('status',[Order::HANDED_OVER,Order::COURIER])
            ->filter($request->all())
            ->where('deliveryman_id', auth('sanctum')->id())
            ->orderBy('id','desc')
            ->paginate($request->perPage ?? 15);

        return OrderResource::collection($orders);
    }

    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $order = $this->model
            ->with([
                'user', 'review', 'point',
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                },
                'orderDetails.deliveryType.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.deliveryAddress',
                'orderDetails.deliveryMan',
                'coupon',
                'userAddress',
                'delivery.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.shop.translation' => fn($q) => $q->actualTranslation($this->lang),
                'transaction.paymentSystem' => function ($q) {
                    $q->select('id', 'tag', 'active');
                },
                'transaction.paymentSystem.translation' => function ($q) {
                    $q->select('id', 'locale', 'payment_id', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.stockExtras.group.translation' => function ($q) {
                    $q->select('id', 'extra_group_id', 'locale', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.countable.translation' => function ($q) {
                    $q->select('id', 'product_id', 'locale', 'title')->actualTranslation($this->lang);
                },])
            ->whereIn('status',[Order::HANDED_OVER,Order::COURIER])
            ->where('deliveryman_id',auth('sanctum')->id())
            ->find($id);
        if ($order){
            return $this->successResponse(__('web.order_found'), OrderResource::make($order));
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
        }elseif (!in_array($request->status,[Order::DELIVERED,Order::CANCELED])){
            return $this->errorResponse(ResponseError::ERROR_253,
                trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->update(['status' => $request->status]);

        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }
}
