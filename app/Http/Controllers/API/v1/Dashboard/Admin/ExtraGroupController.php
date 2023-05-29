<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use Exception;
use App\Models\ExtraGroup;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ExtraGroupResource;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\ExtraRepository\ExtraGroupRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ExtraGroupController extends AdminBaseController
{

    public function __construct(private ExtraGroup $model,private ExtraGroupRepository $groupRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $extras = $this->groupRepository->extraGroupList($request->active ?? null, $request->all());
        return ExtraGroupResource::collection($extras);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function store(Request $request): JsonResponse
    {
        $extra = $this->model->create($request->all());
        if ($extra && isset($request->title)) {
            foreach ($request->title as $index => $title) {
                $extra->translation()->create([
                    'locale' => $index,
                    'title' => $title,
                ]);
            }
            return $this->successResponse(trans('web.extras_list', [], \request()->lang), $extra);
        }
        return $this->errorResponse(trans('web.extras_list', [], \request()->lang), $extra);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $extra = $this->groupRepository->extraGroupDetails($id);
        if ($extra) {
            return $this->successResponse(trans('web.extra_found', [], \request()->lang), ExtraGroupResource::make($extra->load('translations')));
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $extra = $this->model->find($id);
        if ($extra) {
            $extra->update($request->all());
            if (isset($request->title)) {
                $extra->translations()->delete();
                foreach ($request->title as $index => $title) {
                    $extra->translation()->create([
                        'locale' => $index,
                        'title' => $title,
                    ]);
                }
                return $this->successResponse(trans('web.record_has_been_successfully_updated', [], \request()->lang), ExtraGroupResource::make($extra));
            }
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $group = $this->model->find($id);
        if ($group) {
            if (count($group->extraValues) > 0){
                return $this->errorResponse(ResponseError::ERROR_504, trans('errors.' . ResponseError::ERROR_504, [], \request()->lang), Response::HTTP_BAD_REQUEST);
            }
            $group->delete();
            return $this->successResponse(trans('web.record_has_been_successfully_deleted', [], \request()->lang), []);
        }
        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang), Response::HTTP_NOT_FOUND);
    }


    /**
     * ExtraGroup type list.
     *
     * @return JsonResponse
     */
    public function typesList(): JsonResponse
    {
        return $this->successResponse('web.extra_groups_types', $this->model->getTypes());
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function setActive(int $id): JsonResponse|AnonymousResourceCollection
    {
        $group = $this->groupRepository->extraGroupDetails($id);
        if ($group) {
            $group->update(['active' => !$group->active]);

            return $this->successResponse(__('web.record_has_been_successfully_updated'), ExtraGroupResource::make($group));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
