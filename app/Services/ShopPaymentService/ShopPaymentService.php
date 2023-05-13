<?php

namespace App\Services\ShopPaymentService;

use App\Models\ShopPayment;
use App\Services\CoreService;
use App\Helpers\ResponseError;

class ShopPaymentService extends CoreService
{

    protected function getModelClass()
    {
        return ShopPayment::class;
    }

    public function create($collection): array
    {
        $model = $this->model()->create($collection);

        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
    }

    public function update($collection,$id): array
    {
        $model = $this->model()->find($id);

        if ($model)
        {
            $model = $model->update($collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    public function delete(int $id): array
    {
        $model = $this->model()->find($id);
        if ($model)
        {
            $model->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

}
