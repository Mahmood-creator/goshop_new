<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderDetailResource;
use App\Models\User;
use App\Repositories\OrderRepository\OrderDetailRepository;
use App\Services\OrderService\OrderDetailService;
use App\Traits\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderDetailController extends AdminBaseController
{
    use Notification;
    private OrderDetailRepository $detailRepository;

    /**
     * @param OrderDetailRepository $detailRepository
     */
    public function __construct(OrderDetailRepository $detailRepository)
    {
        parent::__construct();
        $this->detailRepository = $detailRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(Request $request)
    {
        $orderDetails = $this->detailRepository->paginate($request->perPage, $request->user_id, $request->all());
        return OrderDetailResource::collection($orderDetails);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        $orderDetail = $this->detailRepository->orderDetailById($id);
        if ($orderDetail) {
            return $this->successResponse(__('web.language_found'), OrderDetailResource::make($orderDetail));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update Order Status details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderDetailStatusUpdate(int $orderDetail, FilterParamsRequest $request)
    {
        $result = (new OrderDetailService())->updateStatus($orderDetail, $request->status ?? null);
        if ($result['status']) {

            // Select User Firebase Token to Push Notification
            $user = User::where('id', $result['data']->order->user_id)->where('settings->notification', 1)->pluck('firebase_token');

            $this->sendNotification($user->toArray(), "Your order status has been changed to $request->status", $result['data']->order->id);

            return $this->successResponse( __('errors.' . ResponseError::NO_ERROR), OrderDetailResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }



    /**
     * Calculate products when cart updated.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateOrderProducts(Request $request)
    {
        $result = $this->detailRepository->orderProductsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }
}
