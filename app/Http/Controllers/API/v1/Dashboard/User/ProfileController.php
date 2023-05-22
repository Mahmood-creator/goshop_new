<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Http\Requests\User\User\UpdateRequest;
use App\Models\Like;
use App\Models\User;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Helpers\ResponseError;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Resources\BannerResource;
use App\Http\Requests\UserCreateRequest;
use App\Services\UserServices\UserService;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\User\User\StoreRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Repositories\UserRepository\UserRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProfileController extends UserBaseController
{
    /**
     * @param UserRepository $userRepository
     * @param UserService $userService
     */
    public function __construct(private UserRepository $userRepository,private UserService $userService)
    {
        parent::__construct();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $collection = $request->validated();

        $result = $this->userService->create($collection);

        if ($result['status']) {
            return $this->successResponse(__('web.record_successfully_created'), $request['data']);
        }
        return $this->errorResponse(
            $result['code'], $result['message'] ?? trans('errors.' . $result['code'], [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = $this->userRepository->userById(auth('sanctum')->id());
        if ($user) {
            return $this->successResponse(__('web.user_found'), UserResource::make($user));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function update(UpdateRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $collection = $request->validated();

        $result = $this->userService->update(auth('sanctum')->user(), $collection);

        if ($result['status']){
            return $this->successResponse(__('web.user_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse
     */
    public function delete(): JsonResponse
    {
        $user = $this->userRepository->userByUUID(auth('sanctum')->user()->uuid);
        if ($user) {
            $user->delete();
            return $this->successResponse(__('web.record_has_been_successfully_deleted'), []);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function fireBaseTokenUpdate(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $user = User::firstWhere('uuid', auth('sanctum')->user()->uuid);
        if ($user) {
            $user->update(['firebase_token' => $request->firebase_token]);
            return $this->successResponse(__('web.record_has_been_successfully_updated'), []);
        } else {
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], \request()->lang ?? 'en'),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    public function passwordUpdate(PasswordUpdateRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $result = $this->userService->updatePassword(auth('sanctum')->user()->uuid, $request->password);
        if ($result['status']){
            return $this->successResponse(__('web.user_password_updated'), UserResource::make($result['data']));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, $result['message'] ?? trans('errors.' . ResponseError::ERROR_404, [], \request()->lang),
            Response::HTTP_BAD_REQUEST
        );
    }

    public function likedLooks(FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $user = $this->userRepository->userById(auth('sanctum')->id());
        if ($user) {
            $likes = Like::where(['likable_type' => Banner::class, 'user_id' => $user->id])->pluck('likable_id');
            $looks = Banner::whereIn('id', $likes)->paginate($request->perPaage ?? 15);

            return $this->successResponse(__('web.list_of_looks'), BannerResource::collection($looks));
        }
        return $this->errorResponse(
            ResponseError::ERROR_404,  trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
            Response::HTTP_NOT_FOUND
        );
    }
}
