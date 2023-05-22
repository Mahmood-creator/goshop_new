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
     * @return string
     */
    protected function getModelClass(): string
    {
        return Model::class;
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $collection
     * @return array
     */
    public function create(array $collection): array
    {
        $collection['password'] = bcrypt($collection['password']);

        $collection['ip_address'] = request()->ip();

        $user = $this->model()->create($collection);

        if (isset($collection['images'])) {
            $user->uploads($collection['images']);
            $user->update(['img' => $collection['images'][0]]);
        }

        $user->syncRoles($collection['role'] ?? 'user');

        (new UserWalletService())->create($user);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];

    }

    public function update($user, $collection): array
    {
        if (isset($collection['password'])) {
            $collection['password'] = bcrypt($collection['password']);
        }

        $item = $user->update($collection);

        if ($item && isset($collection['images'])) {
            $user->galleries()->delete();
            $user->update(['img' => $collection['images'][0]]);
            $user->uploads($collection['images']);
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
    }

    public function updatePassword($uuid, $password): array
    {
        $user = $this->model()->firstWhere('uuid', $uuid);
        if ($user) {

            $user->update(['password' => bcrypt($password)]);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $user];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function addReviewToDeliveryMan($orderId, $collection): array
    {
        $order = Order::find($orderId);
        if ($order?->deliveryMan && Order::DELIVERED) {
            $order->deliveryMan->addReview($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $order];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_432];
    }


}
