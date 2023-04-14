<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\WalletHistoryResource;
use App\Repositories\WalletRepository\WalletHistoryRepository;
use App\Services\WalletHistoryService\WalletHistoryService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WalletHistoryController extends AdminBaseController
{
    private WalletHistoryService $walletHistoryService;
    private WalletHistoryRepository $walletHistoryRepository;

    public function __construct(WalletHistoryService $walletHistoryService, WalletHistoryRepository $walletHistoryRepository)
    {
        parent::__construct();
        $this->walletHistoryService = $walletHistoryService;
        $this->walletHistoryRepository = $walletHistoryRepository;
    }

    public function paginate(Request $request)
    {
        $walletHistory = $this->walletHistoryRepository->walletHistoryPaginate($request->perPage ?? 15, $request->all());
        return WalletHistoryResource::collection($walletHistory);
    }

    public function changeStatus(string $uuid, Request $request)
    {
        if (!isset($request->status) || !in_array($request->status, ['rejected', 'paid'])) {
            return $this->errorResponse(ResponseError::ERROR_253, trans('errors.' . ResponseError::ERROR_253, [], \request()->lang ?? config('app.locale')),
                Response::HTTP_BAD_REQUEST
            );
        }

        $result = $this->walletHistoryService->changeStatus($uuid, $request->status);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_updated'), []);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }
}
