<?php

namespace App\Services\ShopLocationService;

use App\Helpers\ResponseError;
use App\Models\Shop;
use App\Models\ShopLocation;
use App\Services\CoreService;


class ShopLocationService extends CoreService
{

    protected function getModelClass()
    {
        return ShopLocation::class;
    }

    /**
     * Create a new Shop model.
     * @param $collection
     * @return array
     */
    public function create($collection): array
    {
        $model = $this->model()->create($collection);

        if ($model) {
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];

    }

    /**
     * Update specified Shop model.
     * @param array $collection
     * @param int $id
     * @return array
     */
    public function update(array $collection, int $id): array
    {
        $model = $this->model()->find($id);
        if ($model) {

            $model->update($collection);

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * @param array $ids
     * @return array
     */
    public function destroy(array $ids): array
    {
        $items = $this->model()->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_511];
    }
}
