<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\City\IndexRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
        $model = $this->model->select('id','name','status')->where('status',1)->filter($collection)->paginate($collection['perPage']);
        return CityResource::collection($model);
    }
}
