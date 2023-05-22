<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Models\Country;
use App\Traits\ApiResponse;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Rest\Country\IndexRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    use ApiResponse;

    private Country $model;

    public function __construct(Country $model)
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
        $countries = $this->model->select('id','name','status')->filter($collection)->paginate($collection['perPage']);
        return CountryResource::collection($countries);
    }

    public function changeStatus(int $id): JsonResponse|AnonymousResourceCollection
    {
        $country = Country::find($id);
        if ($country){
            $country->update(['status' => !$country->status]);
            return $this->successResponse( __('web.record_was_successfully_change'),CountryResource::make($country));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
