<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\PointResource;
use App\Models\Point;
use Symfony\Component\HttpFoundation\Response;

class PointController extends AdminBaseController
{
    private Point $model;

    /**
     * @param $model
     */
    public function __construct(Point $model)
    {
        parent::__construct();
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request)
    {
        $points = $this->model
            ->orderBy($request->column ?? 'id', $request->sort ?? 'desc')
            ->paginate($request->perPage ?? 15);

        return PointResource::collection($points);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param FilterParamsRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function store(FilterParamsRequest $request)
    {
        $point = $this->model->create([
            'type' => $request->type,
            'price' => $request->price,
            'value' => $request->value ?? 0,
        ]);

        if ($point) {
            return $this->successResponse( __('web.record_was_successfully_create'), PointResource::make($point));
        }
        return $this->errorResponse(
           ResponseError::ERROR_400,  trans('errors.' . ResponseError::ERROR_400, [], \request()->lang ?? config('app.locale')),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function show(int $id)
    {
        $point = $this->model->find($id);
        if ($point) {
            return $this->successResponse(__('web.product_found'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $point = $this->model->find($id);
        if ($point) {
            $point->update([
                'type' => $request->type ?? 'fix',
                'price' => $request->price ?? 0,
                'value' => $request->value ?? 0,
            ]);

            return $this->successResponse(__('web.record_was_successfully_update'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $point = $this->model->find($id);
        if ($point) {
            $point->delete();

            return $this->successResponse(__('web.record_was_successfully_deleted'), []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(int $id)
    {
        $point = $this->model->find($id);
        if ($point) {
            $point->update(['active' => !$point->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), PointResource::make($point));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? config('app.locale')),
            Response::HTTP_NOT_FOUND
        );
    }
}
