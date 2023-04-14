<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filter\FilterRequest;
use App\Repositories\FilterRepository\FilterRepository;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    private FilterRepository $repository;

    public function __construct(FilterRepository $repository)
    {
        $this->repository = $repository;
    }

    public function filter(FilterRequest $request)
    {
        $collection = $request->validated();

        $filter = $this->repository->filter($collection);
        return $filter;

    }
}
