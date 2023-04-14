<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Resources\CategoryResource;
use App\Repositories\Interfaces\CategoryRepoInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends RestBaseController
{

    private CategoryRepoInterface $categoryRepo;

    /**
     * @param CategoryRepoInterface $categoryRepo
     */
    public function __construct(CategoryRepoInterface $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function paginate(Request $request) {
        $categories = $this->categoryRepo->parentCategories($request->perPage ?? 15, true,  $request->all());
        return CategoryResource::collection($categories);
    }


    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid)
    {
        $category = $this->categoryRepo->categoryByUuid($uuid);
        if ($category){
            return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), CategoryResource::make($category));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function categoriesSearch(Request $request)
    {
        $categories = $this->categoryRepo->categoriesSearch($request->search ?? '', true);
        return $this->successResponse(__('errors.'. ResponseError::NO_ERROR), CategoryResource::collection($categories));
    }


}
