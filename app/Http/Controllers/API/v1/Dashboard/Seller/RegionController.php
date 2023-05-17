<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Models\Region;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
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
        $model = $this->model->select('id','name','status')->where('status',1)->filter($collection)->paginate($collection['perPage']);
        return RegionResource::collection($model);
    }

}
