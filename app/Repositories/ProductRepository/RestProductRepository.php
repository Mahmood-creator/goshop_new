<?php

namespace App\Repositories\ProductRepository;

use App\Models\OrderProduct;
use App\Models\Product;
use App\Repositories\CoreRepository;
use Illuminate\Support\Facades\DB;

class RestProductRepository extends CoreRepository
{

    private string $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function productsMostSold($perPage, $array = [])
    {
        return $this->model()->filter($array)->updatedDate($this->updatedDate)
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->without('countable')
            ->withAvg('reviews', 'rating')
            ->withCount('orders')
            ->with([
                'stocks.countable',
                'stocks.discount',
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
            ])
            ->whereHas('stocks', function ($item) {
                $item->where('quantity', '>', 0)->where('price', '>', 0);
            })
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->whereHas('category')
            ->limit(10)
            ->whereActive(1)
            ->paginate($perPage);

    }

    /**
     * @param $perPage
     * @param array $array
     * @return mixed
     */
    public function productsDiscount($perPage, array $array = []): mixed
    {
        $profitable = isset($array['profitable']) ? '=' : '>=';

        return $this->model()->filter($array)->updatedDate($this->updatedDate)
            ->whereHas('discount', function ($item) use ($profitable) {
                $item->where('active', 1)
                    ->whereDate('start', '<=', today())
                    ->whereDate('end', $profitable, today()->format('Y-m-d'));
            })
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->whereHas('stocks', function ($item) {
                $item->where('quantity', '>', 0)->where('price', '>', 0);
            })
            ->withAvg('reviews', 'rating')
            ->whereHas('category')
            ->with([
                'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0), 'stocks.stockExtras.group.translation' => fn($q) => $q->where('locale', $this->lang),
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'product_id', 'locale', 'title'),
                'category' => fn($q) => $q->select('id', 'uuid'),
                'category.translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'category_id', 'locale', 'title'),
                'brand' => fn($q) => $q->select('id', 'title'),
            ])
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->whereActive(1)
            ->paginate($perPage);
    }

    public function getByBrandId($perPage, int $brandId)
    {
        return $this->model()->with([
            'stocks' => fn($q) => $q->where('quantity', '>', 0)->where('price', '>', 0),
            'translation' => fn($q) => $q->actualTranslation($this->lang),
        ])->whereHas('stocks', function ($item) {
            $item->where('quantity', '>', 0)->where('price', '>', 0);
        })
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->where('brand_id', $brandId)
            ->paginate($perPage);
    }

    public function buyWithProduct(int $id)
    {
        $orderDetailsIds = OrderProduct::whereHas('stock', function ($q) use ($id) {
            $q->whereHas('countable', function ($b) use ($id) {
                $b->where('id', $id);
            });
        })->pluck('order_detail_id');

        $productIds = DB::table('order_products as o_p')
            ->leftJoin('stocks as s', 's.id', '=', 'o_p.stock_id')
            ->leftJoin('products as p', 'p.id', '=', 's.countable_id')
            ->select('stock_id', DB::raw('COUNT(stock_id) as stock_count'), 'p.id')
            ->groupBy('stock_id')
            ->orderBy('stock_count', 'desc')
            ->whereIn('order_detail_id', $orderDetailsIds)
            ->where('p.id', '!=', $id)
            ->where('s.quantity', '>', 0)
            ->where('s.price', '>', 0)
            ->take(3)
            ->pluck('p.id');

        return $this->model()->with([
            'stocks',
            'translation' => fn($q) => $q->actualTranslation($this->lang),
        ])
            ->whereHas('shop', function ($item) {
                $item->whereNull('deleted_at')->where('status', 'approved');
            })
            ->find($productIds);
    }
}
