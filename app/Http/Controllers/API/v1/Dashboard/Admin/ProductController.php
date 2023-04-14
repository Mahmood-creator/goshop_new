<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;


use App\Exports\ProductExport;
use App\Helpers\ResponseError;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ReportChartRequest;
use App\Http\Requests\ReportCompareRequest;
use App\Http\Requests\ReportPaginateRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\LanguageResource;
use App\Http\Resources\ProductResource;
use App\Imports\ProductImport;
use App\Jobs\ImportReadyNotify;
use App\Models\Product;
use App\Models\Stock;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\ProductRepository\StockRepository;
use App\Services\ProductService\ProductAdditionalService;
use App\Services\ProductService\ProductService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AdminBaseController
{

    /**
     * @param ProductService $productService
     * @param ProductRepoInterface $productRepository
     */
    public function __construct(
        private ProductService $productService,
        private ProductRepoInterface $productRepository,
        private StockRepository $stockRepository
    )
    {
        parent::__construct();
    }

    public function paginate(Request $request)
    {
        $products = $this->productRepository->productsPaginate($request->perPage ?? 15, $request->active, $request->all());
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->productService->create($request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_create'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $uuid)
    {
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            return $this->successResponse(__('web.product_found'), ProductResource::make($product->load('translations')));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $uuid)
    {
        $result = $this->productService->update($uuid, $request);
        if ($result['status']) {
            return $this->successResponse( __('web.record_was_successfully_update'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $uuid)
    {
        $result = $this->productService->delete($uuid);

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProductProperties(string $uuid, Request $request)
    {
        $result = (new ProductAdditionalService())->createOrUpdateProperties($uuid, $request->all());

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProductExtras(string $uuid, Request $request)
    {
        $result = (new ProductAdditionalService())->createOrUpdateExtras($uuid, $request->all());

        if ($result['status']) {
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($result['data']));
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Add Product Properties.
     *
     * @param string $uuid
     * @param Request $request
     */
    public function addInStock(string $uuid, Request $request)
    {
        $product = Product::firstWhere('uuid', $uuid);
        if ($product) {
            // Polymorphic relation in Countable (Trait)
            $product->addInStock($request, $product->id);
            return $this->successResponse(__('web.record_has_been_successfully_created'), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function productsSearch(Request $request)
    {
        $categories = $this->productRepository->productsSearch($request->search ?? '');
        return ProductResource::collection($categories);
    }

    /**
     * Change Active Status of Model.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function setActive(string $uuid)
    {
        $product = $this->productRepository->productByUUID($uuid);
        if ($product) {
            $product->update(['active' => !$product->active]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), ProductResource::make($product));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function fileExport(Request $request)
    {
        $fileName = 'export/product'.Str::slug(Carbon::now()->format('Y-m-d h:i:s')).'.xls';
        $file = Excel::store(new ProductExport($request->shop_id), $fileName, 'public');
        if ($file) {
            return $this->successResponse('Successfully exported', [
                'path' => 'public/export',
                'file_name' => $fileName
            ]);
        }
        return $this->errorResponse('Error during export');
    }

    public function fileImport(ExportRequest $request)
    {

        $collection = $request->validated();
        try {
            Excel::import(new ProductImport($collection['shop_id']), $request->file);
            return $this->successResponse('Successfully imported');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $exception) {
            $failures = $exception->failures();
            $result = LazyCollection::make(function () use ($failures) {
                foreach ($failures as $failure) {
                    yield [$failure->row(), $failure->attribute(), $failure->errors(), $failure->values()];
                }
            })
                ->collect()
                ->toArray();
            Log::error('failures', $result);
        }
    }

    public function deleteAll(Request $request)
    {
        $result = $this->productService->deleteAll($request->productIds);
        if ($result)
            return $this->successResponse(__('web.record_has_been_successfully_delete'));
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productReportChart(ReportChartRequest $request)
    {
        try {
            $result = $this->productRepository->productReportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportCompare()
    {
        try {
            $result = $this->productRepository->productReportCompareCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->productRepository->reportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productStockReport($product)
    {
        try {
            $result = $this->stockRepository->productStockReportCache($product);

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function productExtrasReport($product)
    {
        try {
            $result = $this->productRepository->isPossibleCacheProductExtrasReport($product);

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function stockReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->stockRepository->stockReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportPaginate(FilterParamsRequest $filterParamsRequest)
    {
        try {
            $result = $this->stockRepository->variationsReportPaginate($filterParamsRequest->get('perPage', 15));

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportChart()
    {
        try {
            $result = $this->stockRepository->variationsReportChartCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

    public function variationsReportCompare()
    {
        try {
            $result = $this->stockRepository->variationsReportCompareCache();

            return $this->successResponse('', $result);
        } catch (\Exception $exception) {
            return $this->errorResponse(ResponseError::ERROR_400, $exception->getMessage());
        }
    }

}
