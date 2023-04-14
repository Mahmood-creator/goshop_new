<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Traits\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        return $this->model->with([
            'translation' => fn($q) => $q->where('locale', $this->lang ?? 'aze'),
            'delivery'
        ])
            ->paginate($request->perPage ?? 10);
    }

    /**
     * Display Terms & Condition.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $model = Country::with([
            'translation' => fn($q) => $q->where('locale', $this->lang ?? 'aze'),
            'delivery'
        ])->find($id);
        if ($model){
            return $this->successResponse(__('web.model_found'), $model);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang ?? 'en'),
            Response::HTTP_NOT_FOUND
        );
    }
}
