<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rest\Review\IndexRequest;
use App\Http\Resources\ReviewResource;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReviewController extends RestBaseController
{
    private Review $model;

    /**
     * @param Review $model
     */
    public function __construct(Review $model)
    {
        $this->model = $model;
    }

    public function paginate(IndexRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $reviews = $this->model->with(['reviewable', 'user'])
            ->where('reviewable',Product::class)
            ->where('reviewable_id',$collection['product_id'])
            ->orderBy('id', 'desc')
            ->paginate($collection['perPage']);

        return ReviewResource::collection($reviews);
    }
}
