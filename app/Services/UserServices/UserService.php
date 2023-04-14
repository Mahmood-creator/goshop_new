<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\Order;
use App\Models\User as Model;
use App\Services\CoreService;
use App\Services\Interfaces\UserServiceInterface;

class UserService extends CoreService implements UserServiceInterface
{
    /**
     * @return mixed
     */
    protected function getModelClass()
    {
        return Model::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $collection
     * @return mixed
     */
    public function create($collection)
    {
        try {
            $user = $this->model()->create(
                $this->setUserParams($collection) + [
                    'password' => bcrypt($collection->password ?? 'password'),
                    'ip_address' => request()->ip()
                ]);

            if (isset($collection->images)) {
                $user->uploads($collection->images);
                $user->update(['img' => $collection->images[0]]);
            }
            $user->syncRoles($collection->role ?? 'user');

            (new UserWalletService())->create($user);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update(string $uuid, $collection)
    {
        $user = $this->model()->firstWhere('uuid', $uuid);
        if ($user) {
            try {
                $item = $user->update($this->setUserParams($collection, $user));
                if (isset($collection->password)) {
                    $user->update([
                        'password' => bcrypt($collection->password)
                    ]);
                }
                if ($item && isset($collection->images)) {
                    $user->galleries()->delete();
                    $user->update(['img' => $collection->images[0]]);
                    $user->uploads($collection->images);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function updatePassword($uuid, $password)
    {
        $user = $this->model()->firstWhere('uuid', $uuid);
        if ($user) {
            try {
                $user->update(['password' => bcrypt($password)]);

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
            } catch (\Exception $e) {
                return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
            }
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function addReviewToDeliveryMan($orderId, $collection)
    {
        $order = Order::find($orderId);
        if ($order?->deliveryMan && Order::DELIVERED) {
            $order->deliveryMan->addReview($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_432];
    }

    public function setUserParams($collection, $user = null): array
    {
        return [
            'firstname' => isset($user) ? $collection->firstname ?? $user->firstname : $collection->firstname ?? null,
            'lastname' => isset($user) ? $collection->lastname ?? $user->lastname : $collection->lastname ?? null,
            'email' => isset($user) ? $collection->email ?? $user->email : $collection->email ?? null,
            'phone' => isset($user) ? $collection->phone ?? $user->phone : $collection->phone ?? null,
            'birthday' => isset($user) ? $collection->birthday ?? $user->birthday : $collection->birthday ?? null,
            'gender' => isset($user) ? $collection->gender ?? $user->gender : $collection->gender ?? null ?? 'male',
            'firebase_token' => isset($user) ? $collection->firebase_token ?? $user->firebase_token : $collection->firebase_token ?? null,
            'active' => 1
        ];
    }

}
