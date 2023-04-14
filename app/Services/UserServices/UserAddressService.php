<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserAddressService extends \App\Services\CoreService
{
    const FINDEX_URL = 'https://api2.findex.az/api/partners';
    const SECRET_KEY = 'pasMallSi';

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
        $address = $this->model()->create($this->setAddressParams($collection));

        $this->setDefault($address->id, $address->default);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
    }

    public function createFindex($collection, $userID): array
    {
        $user = User::find($userID);

        $collection['user_id'] = $userID;

        if (!$user->user_delivery_id) {
            $result = $this->createUserFindex($collection);

            if (!$result['status']) {
                return ['status' => false, 'code' => $result['code'], 'message' => $result['message']];
            }

            $user->update([
                'birthday' => $collection['birth_date'],
                'gender' => $collection['gender'],
                'address' => $collection['address'],
                'passport_number' => $collection['passport_number'],
                'passport_secret' => $collection['passport_secret'],
                'user_delivery_id' => $result['data']
            ]);

        }

        $address = $this->model()->create($this->setAddressParams($collection));

        $this->setDefault($address->id, $address->default);


        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
    }

    public function updateFindex($collection, $id): array
    {
        $address = $this->model()->find($id);

        if ($address) {

            $addressExists = $address->orders()->whereIn('status', [
                Order::NEW,
                Order::READY,
                Order::DECLARATION_IN_ADVANCE,
                Order::EXTERNAL_WAREHOUSE,
                Order::ON_THE_WAY,
                Order::AT_CUSTOMS,
                Order::INTERNAL_WAREHOUSE,
                Order::HANDED_OVER,
                Order::COURIER,
            ])->exists();

            if ($addressExists) {
                return ['status' => false, 'code' => ResponseError::ERROR_433];
            }

            $address->update($this->setAddressParams($collection));
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function update($id, $collection): array
    {
        $model = $this->model()->find($id);
        if ($model) {
            $model->update($this->setAddressParams($collection));

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function destroy(int $id)
    {
        $model = $this->model()->find($id);

        if ($model) {
            $addressExists = $model->orders()->whereIn('status', [
                Order::NEW,
                Order::READY,
                Order::DECLARATION_IN_ADVANCE,
                Order::EXTERNAL_WAREHOUSE,
                Order::ON_THE_WAY,
                Order::AT_CUSTOMS,
                Order::INTERNAL_WAREHOUSE,
                Order::HANDED_OVER,
                Order::COURIER,
            ])->exists();

            if ($addressExists) {
                return ['status' => false, 'code' => ResponseError::ERROR_433];
            }

            $model->delete();

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * Set User Address model parameters for actions
     */
    private function setAddressParams($collection): array
    {
        return [
            'user_id' => $collection['user_id'],
            'title' => $collection['address'],
            'location' => [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ],
            'active' => $collection['active'] ?? 0,
            'number' => $collection['number'] ?? null,
            'country_id' => $collection['country_id'] ?? null,
            'province' => $collection['province'] ?? null,
            'apartment' => $collection['apartment'] ?? null,
            'company_name' => $collection['company_name'] ?? null,
            'postcode' => $collection['postcode'] ?? null,
            'city' => $collection['city'] ?? null,
            'note' => $collection['note'] ?? null,
            'name' => $collection['name'] ?? null,
            'surname' => $collection['surname'] ?? null,
            'email' => $collection['email'] ?? null,
        ];
    }

    public function setAddressDefault(int $id = null, int $default = null): array
    {
        $item = $this->model()->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($item) {
            return $this->setDefault($id, $default, auth('sanctum')->id());
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function createUserFindex($collection)
    {
        $response = Http::withHeaders(['application/json'])
            ->post(self::FINDEX_URL . '/create_user?secret=' . self::SECRET_KEY, [
                'name' => $collection['name'],
                'surname' => $collection['surname'],
                'birth_date' => $collection['birth_date'],
                'gender' => $collection['gender'],
                'email' => $collection['email'],
                'address' => $collection['address'],
                'passport_number' => $collection['passport_number'],
                'passport_secret' => $collection['passport_secret'],
                'number' => $collection['number'],
            ]);
        if ($response->successful()) {
            return ['status' => true, 'code' => ResponseError::ERROR_200, 'data' => $response->json('user_id')];
        }

        if ($response->status() == 422) {
            $array = $response->json(['errors']);
            return ['status' => false, 'code' => ResponseError::ERROR_430, 'message' => reset($array)[0]];
        }

        if ($response->status() == 400) {
            $array = $response->json(['errors']);
            return ['status' => false, 'code' => ResponseError::ERROR_430, 'message' => reset($array)[0]];
        }

        if ($response->status() == 500) {
            return ['status' => false, 'code' => ResponseError::ERROR_509, 'message' => 'Daxili server xətası'];
        }
    }
}
