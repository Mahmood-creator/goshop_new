<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CityController extends Controller
{
    use ApiResponse;

    private City $model;

    public function __construct(City $model)
    {
        $this->model = $model;
        $this->lang = request('lang') ?? null;
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
