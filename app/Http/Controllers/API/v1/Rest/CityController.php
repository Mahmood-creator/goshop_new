<?php

namespace App\Http\Controllers\Api\v1\Rest;

use App\Models\City;
use App\Traits\ApiResponse;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CityResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\City\IndexRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    use ApiResponse;
    private City $model;

    public function __construct(City $model)
    {
        $this->model = $model;
    }

    /**
     * Display a listing of the FAQ.
     *
     * @param IndexRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $cities = $this->model->select('id','name','status')->filter($collection)->paginate($collection['perPage']);
        return CityResource::collection($cities);

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
