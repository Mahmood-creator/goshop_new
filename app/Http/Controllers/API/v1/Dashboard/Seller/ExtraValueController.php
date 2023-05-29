<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\Seller\ExtraValue\StoreRequest;
use App\Http\Requests\Seller\ExtraValue\UpdateRequest;
use App\Http\Resources\ExtraValueResource;
use App\Models\ExtraValue;
use App\Repositories\ExtraRepository\ExtraValueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ExtraValueController extends SellerBaseController
{
    /**
     * @param ExtraValue $model
     * @param ExtraValueRepository $valueRepository
     */
    public function __construct(private ExtraValue $model, private ExtraValueRepository $valueRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $values = $this->valueRepository->extraValueList(
            $request->active ?? null,
            $request->group_id ?? null,
            $request->perPage ?? 15,
            $request->search ?? null);

        return ExtraValueResource::collection($values);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $collection['created_by'] = $this->shop->id;

        $value = ExtraValue::create($collection);

        if (isset($collection['images'])) {
            $value->uploads($collection['images']);
        }
        return $this->successResponse(trans('web.record_has_been_successfully_created', [], \request()->lang), ExtraValueResource::make($value));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function show(int $id): JsonResponse|AnonymousResourceCollection
    {
        $extraValue = $this->valueRepository->extraValueDetails($id);
        if ($extraValue) {
            return $this->successResponse(trans('web.extra_value_found', [], \request()->lang), ExtraValueResource::make($extraValue));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $value = $this->model->find($id);
        $collection = $request->validated();
        $collection['created_by'] = $this->shop->id;
        if ($value) {
            $value->update($collection);
            return $this->successResponse(trans('web.record_has_been_successfully_updated', [], \request()->lang), ExtraValueResource::make($value));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $value = $this->model->where('created_by', '=', $this->shop->id)->find($id);
        if ($value) {
            if (count($value->stocks) > 0) {
                return $this->errorResponse(ResponseError::ERROR_504, trans('errors.' . ResponseError::ERROR_504, [], \request()->lang), Response::HTTP_BAD_REQUEST);
            }
            $value->delete();
            return $this->successResponse(trans('web.record_has_been_successfully_deleted', [], \request()->lang), []);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(int $id): JsonResponse|AnonymousResourceCollection
    {
        $value = $this->model->find($id);
        if ($value) {
            $value->update(['active' => !$value->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), ExtraValueResource::make($value));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
