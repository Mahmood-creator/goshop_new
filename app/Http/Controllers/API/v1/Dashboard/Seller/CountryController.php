<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\Country\IndexRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
        $countries = $this->model->select('id','name','status')->where('status',1)->filter($collection)->paginate($collection['perPage']);
        return CountryResource::collection($countries);
    }
}
