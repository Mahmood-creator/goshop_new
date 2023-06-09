<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\City;
use App\Traits\ApiResponse;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
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
        $this->lang = request('lang') ?? null;
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

    public function changeStatus(int $id): JsonResponse|AnonymousResourceCollection
    {
        $model = City::find($id);
        if ($model){
            $model->update(['status' => !$model->status]);
            return $this->successResponse( __('web.record_was_successfully_change'),CityResource::make($model));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
