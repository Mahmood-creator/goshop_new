<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\UserAddress;
use Illuminate\Support\Str;

class UserAddressService extends \App\Services\CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return UserAddress::class;
    }

    public function create($collection): array
    {
        $collection['location'] = [
            'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
            'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
        ];

        $address = $this->model()->create($collection);

        $this->setDefault($address->id, $address->default);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
    }

    public function update($id, $collection): array
    {
        $model = $this->model()->find($id);
        if ($model) {
            $collection['location'] = [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ];
            $model->update($collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /* Have to change */
    public function delete(int $id)
    {
        $model = $this->model()->find($id);

        if ($model) {
            $addressExists = $model->orders()->whereIn('status', [
                Order::NEW,
                Order::READY,
            ])->exists();

            if ($addressExists) {
                return ['status' => false, 'code' => ResponseError::ERROR_433];
            }

            $model->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


    public function setAddressDefault(int $id = null, int $default = null): array
    {
        $item = $this->model()->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($item) {
            return $this->setDefault($id, $default, auth('sanctum')->id());
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

}
