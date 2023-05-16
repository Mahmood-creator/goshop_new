<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
