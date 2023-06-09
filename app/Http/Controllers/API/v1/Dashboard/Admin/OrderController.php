<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Repositories\ProductTypeRepository\ProductTypeRepository;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService\OrderStatusUpdateService;
use App\Traits\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Exception;

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
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $orders = $this->orderRepository->ordersList();

        return OrderResource::collection($orders);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->orderRepository->ordersPaginate($request->perPage ?? 15, $request->user_id ?? null, $request->all());

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
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
    public function show(int $id): JsonResponse
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


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
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
     * @param int $orderId
     * @param Request $request
     * @return JsonResponse
     */
    public function orderDeliverymanUpdate(int $orderId, Request $request): JsonResponse
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
    public function orderStatusChange(int $id, Request $request): JsonResponse
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

    /**
     * @param int $id
     * @return \Illuminate\Http\Response|JsonResponse|Application|ResponseFactory
     * @throws FileNotFoundException
     */
    public function exportTicketPdf(int $id): \Illuminate\Http\Response|JsonResponse|Application|ResponseFactory
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


    public function ordersReportChart(): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->orderRepository->orderReportChartCache();
            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function ordersReportPaginate(FilterParamsRequest $filterParamsRequest): JsonResponse|AnonymousResourceCollection
    {
        try {
            $result = $this->orderRepository->ordersReportPaginate($filterParamsRequest->get('perPage', 15));
            return $this->successResponse('', $result);
        } catch (Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }
}
