<?php

namespace App\Services\CategoryServices;

use App\Helpers\ResponseError;
use App\Models\Category;
use App\Services\CoreService;
use App\Services\Interfaces\CategoryServiceInterface;

class CategoryService extends CoreService implements CategoryServiceInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Category::class;
    }

    /**
     * @param $collection
     * @return array
     */
    public function create(array $collection)
    {
        try {
            $parentId = data_get($collection, 'parent_id', 0);

            $parentCategory = Category::find($parentId);

            if (data_get($parentCategory, 'product')) {
                return ['status' => false, 'code' => ResponseError::ERROR_111];
            }

            /** @var Category $category */
            $category = $this->model()->create($this->setCategoryParams($collection));

            $this->setTranslations($category, $collection);

            if (data_get($collection, 'images.0')) {
                $category->update(['img' => data_get($collection, 'images.0', '')]);
                $category->uploads(data_get($collection, 'images', []));
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }

    }

    /**
     * @param string $uuid
     * @param $collection
     * @return array
     */
    public function update(string $uuid, $collection): array
    {
        try {
            $parentId = data_get($collection, 'parent_id', 0);

            $parentCategory = Category::find($parentId);

            if (data_get($parentCategory, 'product')) {
                return ['status' => false, 'code' => ResponseError::ERROR_111];
            }

            $category = $this->model()->firstWhere('uuid', $uuid);
            if ($category) {
                $category->update($this->setCategoryParams($collection));
                $this->setTranslations($category, $collection);
                if (isset($collection['images'])) {
                    $category->galleries()->delete();
                    $category->uploads($collection['images']);
                    $category->update(['img' => $collection['images'][0]]);
                }
                return ['status' => true, 'code' => ResponseError::NO_ERROR];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        } catch (\Exception $e) {
            return ['status' => false, 'code' => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param string $uuid
     * @return array
     */
    public function delete(string $uuid): array
    {
        $item = $this->model()->firstWhere('uuid', $uuid);
        if ($item) {
            if (count($item->children) > 0) {
                return ['status' => false, 'code' => ResponseError::ERROR_504];
            }
            $item->delete();
            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    /**
     * Set Category params for Create & Update function
     * @param $collection
     * @return array
     */
    private function setCategoryParams($collection): array
    {
        return [
            'keywords' => $collection['keywords'] ?? null,
            'parent_id' => $collection['parent_id'] ?? 0,
            'type' => $collection['type'] ?? 1,
            'active' => $collection['active'] ?? 0,
            'weight' => $collection['weight'] ?? 1,
            'product_type_id' => $collection['product_type_id'] ?? null
        ];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection['title'] as $index => $value) {
            if (isset($value) || $value != '') {
                $model->translation()->create([
                    'title' => $value,
                    'description' => data_get($collection, "description.$index"),
                    'locale' => $index,
                ]);
            }
        }
    }
}
