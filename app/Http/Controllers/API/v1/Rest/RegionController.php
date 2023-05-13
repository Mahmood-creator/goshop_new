<?php

namespace App\Http\Controllers\Api\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\Region\IndexRequest;
use App\Models\Region;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegionController extends Controller
{
    use ApiResponse;
    private Region $model;

    public function __construct(Region $model)
    {
        $this->model = $model;
        $this->lang = request('lang') ?? null;
    }

    /**
     * Display a listing of the FAQ.
     *
     * @param IndexRequest $request
     * @return LengthAwarePaginator
     */
    public function index(IndexRequest $request): LengthAwarePaginator
    {
        $collection = $request->validated();
        return $this->model->select('id','name')->filter($collection)->paginate($collection['perPage']);
    }

    /**
     * Display Terms & Condition.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $model = $this->model->select('id','name')->find($id);
        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }
}
