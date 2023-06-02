<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\Filter\FilterRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\User\Product\MostSoldProductRequest;
use App\Http\Requests\User\Product\ProductDiscountRequest;
use App\Http\Resources\ProductResource;
use App\Models\OrderProduct;
use App\Models\Point;
use App\Models\Product;
use App\Repositories\CategoryRepository\CategoryRepository;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\OrderRepository\OrderDetailRepository;
use App\Repositories\ProductRepository\RestProductRepository;
use App\Repositories\ShopRepository\ShopRepository;
use App\Services\ProductService\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends RestBaseController
{
    private ProductRepoInterface $productRepository;
    private RestProductRepository $restProductRepository;

    public function __construct(RestProductRepository $restProductRepository, ProductRepoInterface $productRepository)
    {
        $this->middleware('sanctum.check')->only('addProductReview');
        $this->productRepository = $productRepository;
        $this->restProductRepository = $restProductRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();

        $products = $this->productRepository->productFilter($collection);
        return ProductResource::collection($products);
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        request()->merge([
            'review' => true,
        ]);
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            return $this->successResponse(__('errors.' . ResponseError::NO_ERROR), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productsByShopUuid(FilterParamsRequest $request, string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $shop = (new ShopRepository())->shopDetails($uuid);
        if ($shop) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['shop_id' => $shop->id, 'rest' => true]);
            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productsByBrand(FilterParamsRequest $request, int $id): AnonymousResourceCollection
    {
        $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['brand_id' => $id, 'rest' => true]);
        return ProductResource::collection($products);
    }

    public function productsByCategoryUuid(FilterParamsRequest $request, string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $category = (new CategoryRepository())->categoryByUuid($uuid);
        if ($category) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['category_id' => $category->id, 'rest' => true]);
            return ProductResource::collection($products);
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
     * @return AnonymousResourceCollection
     */
    public function productsSearch(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository->productsSearch($request->search ?? '', true);
        return ProductResource::collection($products);
    }

    public function mostSoldProducts(MostSoldProductRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $products = $this->restProductRepository->productsMostSold($collection);
        return ProductResource::collection($products);
    }

    /**
     * Search Model by tag name.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductReview(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductReviewService())->addReview($uuid, $request);
        if ($result['status']) {
            return $this->successResponse(ResponseError::NO_ERROR, []);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function discountProducts(ProductDiscountRequest $request): AnonymousResourceCollection
    {
        $collection = $request->validated();
        $products = $this->restProductRepository->productsDiscount($collection);
        return ProductResource::collection($products);
    }

    /**
     * @param Request $request
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function productsCalculate(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $result = (new OrderDetailRepository())->orderProductsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }

    /**
     * Get Products by IDs.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsByIDs(Request $request): AnonymousResourceCollection
    {
        $products = $this->productRepository->productsByIDs($request->products);
        return ProductResource::collection($products);
    }

    public function checkCashback(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $point = Point::getActualPoint($request->amount ?? 0);
        return $this->successResponse(__('web.cashback'), ['price' => $point]);
    }

    public function getByBrandId(Request $request,int $id): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->getByBrandId($request->perPage ?? 15,$id);
        return ProductResource::collection($products);
    }

    public function buyWithProduct(int $id)
    {
        $products = $this->restProductRepository->buyWithProduct($id);
        return ProductResource::collection($products);
    }
}
