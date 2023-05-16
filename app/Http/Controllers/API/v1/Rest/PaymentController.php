<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Models\Payment;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PaymentResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\PaymentRepository\PaymentRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends RestBaseController
{
    private PaymentRepository $paymentRepository;

    /**
     * @param Payment $model
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(Payment $model, PaymentRepository $paymentRepository)
    {
        $this->model = $model;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $payments = $this->paymentRepository->paginate(['active' => 1]);
        return PaymentResource::collection($payments);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $payment = $this->paymentRepository->paymentDetails($id);
        if ($payment && $payment->active){
            return $this->successResponse(__('web.payment_found'), PaymentResource::make($payment));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }

}
