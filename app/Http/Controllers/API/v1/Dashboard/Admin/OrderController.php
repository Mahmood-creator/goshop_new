<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Repositories\ProductTypeRepository\ProductTypeRepository;
use App\Services\FindexService\FindexService;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Traits\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AdminBaseController
{
    use Notification;
    private OrderRepoInterface $orderRepository;
    private OrderServiceInterface $orderService;
    private ProductTypeRepository $productTypeRepository;

    /**
     * @param OrderRepoInterface $orderRepository
     * @param OrderServiceInterface $orderService
     * @param ProductTypeRepository $productTypeRepository
     */
    public function __construct(
        OrderRepoInterface $orderRepository,
        OrderServiceInterface $orderService,
        ProductTypeRepository $productTypeRepository
    )
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->productTypeRepository = $productTypeRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $orders = $this->orderRepository->ordersList();

        return OrderResource::collection($orders);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15, $request->user_id ?? null, $request->all());

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->orderService->create($request);
        if ($result['status']) {

            // Select Seller Firebase Token to Push Notification
            $sellers = User::whereHas('shop', function ($q) use($result){
                $q->whereIn('id', $result['data']->orderDetails()->pluck('shop_id'));
            })->whereNotNull('firebase_token')->pluck('firebase_token');

            $this->sendNotification($sellers->toArray(), "New order was created", $result['data']->id);
            return $this->successResponse( __('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $order = $this->orderRepository->orderDetails($id);
        if ($order) {
            return $this->successResponse(__('web.language_found'), OrderResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function allOrderStatusChange(Request $request, int $id)
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
            $this->orderStatusChange($detail->id, $request);
        }
        $data = Order::with('orderDetails')->find($id);

        return $this->successResponse(ResponseError::NO_ERROR, $data);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id, Request $request)
    {
        $result = $this->orderService->update($id, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), OrderResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Update Order Delivery details by OrderDetail ID.
     *
     * @param int $orderDetail
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderDeliverymanUpdate(int $orderId, Request $request)
    {
        $order = $this->orderRepository->orderDetails($orderId);
        if ($order){
            $order->update([
                'deliveryman_id' => $request->deliveryman_id ?? $order->deliveryman_id,
            ]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), OrderDetailResource::make($order));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
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
    public function orderStatusChange(int $id, Request $request)
    {

        $detail = OrderDetail::find($id);
        if ($detail) {
            $result = (new OrderStatusUpdateService())->statusUpdate($detail, $request->status);
            if ($result['status']) {
                return $this->successResponse(ResponseError::NO_ERROR, OrderResource::make($detail));
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

    public function exportTicketPdf(int $id)
    {
        $order = Order::with('orderDetails.orderStocks', 'orderDetails.shop')->find($id);

        $currencyTry = Currency::where('short_code','TRY')->first();

        $currencyUsd = Currency::where('short_code','USD')->first();

        if ($order) {
            $productTypeName = 'Not found';
            $productType = $this->productTypeRepository->productsTypeList()->where('id',$order->product_type_id)->first();
            if ($productType){
                $productTypeName = $productType['name'];
            }
            $pdf = PDF::loadView('order-ticket', compact('order','currencyTry','currencyUsd','productTypeName'));
            $pdf->save(Storage::disk('public')->path('export/invoices') . '/order_ticket.pdf');

            return response(Storage::disk('public')->get('/export/invoices/order_ticket.pdf'), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment',
            ]);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? config('app.locale')));
    }


    public function ordersReportChart()
    {
        try {
            $result = $this->orderRepository->orderReportChartCache();
            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function ordersReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->orderRepository->ordersReportPaginate($filterParamsRequest->get('perPage', 15));
            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
