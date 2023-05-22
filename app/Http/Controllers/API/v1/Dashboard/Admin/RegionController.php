<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Region;
use App\Traits\ApiResponse;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rest\Region\IndexRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * @return AnonymousResourceCollection
     */
    public function index(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $regions = $this->model->select('id','name','status')->filter($collection)->paginate($collection['perPage']);
        return RegionResource::collection($regions);
    }


    public function changeStatus(int $id): JsonResponse|AnonymousResourceCollection
    {
        $model = Region::find($id);
        if ($model){
            $model->update(['status' => !$model->status]);
            return $this->successResponse( __('web.record_was_successfully_change'),RegionResource::make($model));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
