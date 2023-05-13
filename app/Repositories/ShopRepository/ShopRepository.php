<?php

namespace App\Repositories\ShopRepository;

use App\Exports\ShopsReportExport;
use App\Http\Resources\CompareResource;
use App\Jobs\ExportJob;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Repositories\Interfaces\ShopRepoInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ShopRepository extends \App\Repositories\CoreRepository implements ShopRepoInterface
{
    private $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Shop::class;
    }

    /**
     * Get all Shops from table
     */
    public function shopsList(array $array = []) {
        return $this->model()->updatedDate($this->updatedDate)
            ->filter($array)
            ->with([
                'translation' => fn($q) => $q->actualTranslation($this->lang),
                'seller.roles',
                'seller' => function($q) {
                    $q->select('id', 'firstname', 'lastname');
                }
            ])->orderByDesc('id')->orderByDesc('updated_at')->get();
    }

    /**
     * Get one Shop by UUID
     * @param int $perPage
     * @param array $array
     * @return mixed
     */
    public function shopsPaginate(int $perPage, array $array = []): mixed
    {
        return $this->model()->updatedDate($this->updatedDate)
            ->withAvg('reviews', 'rating')
            ->withCount('reviews', 'orders')
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->filter($array)
            ->with([
                'translation' => function($q) {
                    $q->where('locale', $this->lang)
                        ->select('id', 'locale', 'title', 'shop_id');
                },
                'seller.roles',
                'seller' => function($q) {
                    $q->select('id', 'firstname', 'lastname');
                },
            ])
            ->when(isset($array['sort']) && $array['sort'] == 'orders_count', function ($q) {
                $q->orderByDesc('orders_count');
            }, function ($q) {
                $q->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc');
            })
            ->paginate($perPage);
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function shopDetails(string $uuid)
    {
        return $this->model()->query()
            ->withAvg('reviews', 'rating')
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang),
                'subscription',
                'seller.roles',
                'seller' => function($q) {
                    $q->select('id', 'firstname', 'lastname');
                }
            ])->firstWhere('uuid', $uuid);
    }

    /**
     * @param string $uuid
     * @return mixed
     */
    public function show(string $uuid)
    {
        return $this->model()->query()
            ->withAvg('reviews', 'rating')
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang),
                'subscription',
                'seller.roles',
                'seller' => function($q) {
                    $q->select('id', 'firstname', 'lastname');
                }
            ])->firstWhere('uuid', $uuid);
    }

    /**
     * @param string $search
     * @param array $array
     * @return mixed
     */
    public function shopsSearch(string $search, $array = [])
    {
        return $this->model()->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)
        ])
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->whereHas('translation', function ($q) use($search) {
                $q->where('title', 'LIKE', '%'. $search . '%')
                    ->select('id', 'shop_id', 'locale', 'title');
            })
            ->where('status','approved')
            ->filter($array)
            ->latest()->paginate(10);
    }

    /**
     * @param array $ids
     * @param null $status
     * @return mixed
     */
    public function shopsByIDs(array $ids = [], $status = null)
    {
        return $this->model()->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang),
            'deliveries.translation' => fn($q) => $q->actualTranslation($this->lang),
        ])
            ->when(isset($status), function ($q) use ($status) {
                $q->where('status', $status);
            })->find($ids);
    }

    public function getShopWithSellerCache()
    {
        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), 604800,
            function () {
                return $this->getShopsWithSeller();
            });
    }

    public function getShopsWithSeller()
    {
        return Shop::query()
            ->select(['shops.id as shop_id',
                'sht.title as shop_translation_title',
                'seller.firstname as seller_firstname',
                'seller.lastname as seller_lastname',
                DB::raw("CONCAT(seller.firstname, ' ', ifnull(seller.lastname,'')) as seller_full_name"),
                'seller.id as seller_id'])
            ->whereStatus(Shop::APPROVED)
            ->join('shop_translations as sht', function ($join) {
                $join->on('shops.id', '=', 'sht.shop_id')
                    ->where('sht.locale', app()->getLocale());
            })
            ->when(request('sellers'), fn($q) => $q->whereIn('seller.id', request('sellers')))
            ->when(request('shops'), fn($q) => $q->whereIn('shops.id', request('shops')))
            ->join('users as seller', 'shops.user_id', '=', 'seller.id')
            ->orderBy(request('column', 'shop_id'), request('sort', 'desc'))
            ->get();
    }

    public function reportPaginateCache($perPage)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        $perPage = request('export') === 'excel' ? null : $perPage;

        //$data = \Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers',[])) . implode('.', request('shops',[]))), $ttl,
        //    function () use ($dateFrom, $dateTo, $perPage) {
        //        return $this->reportPaginate($dateFrom, $dateTo, $perPage);
        //    });
        $data = $this->reportPaginate($dateFrom, $dateTo, $perPage);

        if (request('export') === 'excel') {
            $name = 'shops-report-' . Str::random(8);
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, ShopsReportExport::class);

            return [
                'path'      => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link'      => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $data;
    }

    public function reportPaginate($dateFrom, $dateTo, $perPage)
    {
        return Shop::query()
            ->select(['shops.id',
                'shops.deleted_at',
                'sht.title as shop_translation_title',
                'seller.firstname as seller_firstname',
                'seller.lastname as seller_lastname',
                'seller.id as seller_id'])
            ->addSelect([
                'products_count'             => Product::whereActive(true)
                    ->whereColumn('shop_id', 'shops.id')
                    ->whereHas('shop', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->selectRaw('IFNULL(COUNT(id), 0)'),
                'completed_orders_count'     => Order::whereHas('orderDetails.products.stock.countable',
                    function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status('completed')
                    ->selectRaw('IFNULL(COUNT(id), 0)'),
                'completed_orders_price_sum' => Order::whereHas('orderDetails.products.stock.countable',
                    function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status('completed')
                    ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2)'),
                'canceled_orders_count'      => Order::whereHas('orderDetails.products.stock.countable',
                    function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status('canceled')
                    ->selectRaw('IFNULL(COUNT(id), 0)'),
                'canceled_orders_price_sum'  => Order::whereHas('orderDetails.products.stock.countable',
                    function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status('canceled')
                    ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2)'),
                'items_sold'                 => OrderProduct::whereHas('stock.countable', function ($product
                ) {
                    $product->whereColumn('shop_id', 'shops.id');
                })
                    ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereStatus(OrderDetail::DELIVERED)
                            ->whereHas('order',
                                fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));
                    })
                    ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0)'),
                'net_sales'                  => OrderDetail::whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock.countable', function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->netSalesSum(),
                'total_earned'               => OrderDetail::whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock.countable', function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2)'),
                'delivery_earned'            => OrderDetail::whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock.countable', function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->selectRaw('TRUNCATE(IFNULL(SUM(delivery_fee),0),2)'),
                'tax_earned'                 => OrderDetail::whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock.countable', function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->selectRaw('TRUNCATE(IFNULL(SUM(tax),0),2)'),
                'commission_earned'          => OrderDetail::whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock.countable', function ($product) {
                        $product->whereColumn('shop_id', 'shops.id');
                    })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->selectRaw('TRUNCATE(IFNULL(SUM(commission_fee),0),2)'),
            ])
            ->whereStatus(Shop::APPROVED)
            ->join('shop_translations as sht', function ($join) {
                $join->on('shops.id', '=', 'sht.shop_id')
                    ->where('sht.locale', app()->getLocale());
            })
            ->join('users as seller', 'shops.user_id', '=', 'seller.id')
            ->when(request('shops'), function ($query) {
                $query->whereIn('shops.id', request('shops'));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereIn('shops.user_id', request('sellers'));
            })
            ->orderBy(request('column', 'id'), request('sort', 'desc'))
            ->whereBetween('shops.created_at', [$dateFrom, $dateTo])
            ->withTrashed()
            ->when($perPage,
                fn($q) => $q->paginate($perPage),
                fn($q) => $q->get());
    }

    public function reportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        //return $this->reportChart($dateFrom, $dateTo);
        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->reportChart($dateFrom, $dateTo);
            });
    }

    public function reportChart($dateFrom, $dateTo)
    {
        $shopsCount              = moneyFormatter($this->shopsCountQuery($dateFrom, $dateTo)->count());
        $productsCount           = moneyFormatter($this->productsCountQuery($dateFrom, $dateTo)->count());
        $itemsSold               = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo)
            ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0) as quantities_sum')
            ->value('quantities_sum'));
        $netSales                = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo)
            ->netSalesSum()
            ->value('net_sales_sum'));
        $completedOrdersCount    = moneyFormatter($this->ordersQuery($dateFrom, $dateTo)
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));
        $completedOrdersPriceSum = moneyFormatter($this->ordersQuery($dateFrom, $dateTo)
            ->selectRaw('TRUNCATE(sum(price),2) as orders_price_sum')
            ->value('orders_price_sum'));
        $canceledOrdersCount     = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, null, 'canceled')
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));
        $canceledOrdersPriceSum  = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, null, 'canceled')
            ->selectRaw('TRUNCATE(sum(price),2) as orders_price_sum')
            ->value('orders_price_sum'));
        $totalEarned             = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo)
            ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2) as total_earned')
            ->value('total_earned'));
        $deliveryEarned          = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo)
            ->selectRaw('TRUNCATE(IFNULL(SUM(delivery_fee),0),2) as delivery_earned')
            ->value('delivery_earned'));
        $taxEarned               = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo)
            ->selectRaw('TRUNCATE(IFNULL(SUM(tax),0),2) as tax_earned')
            ->value('tax_earned'));
        $commissionEarned        = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo)
            ->selectRaw('TRUNCATE(IFNULL(SUM(commission_fee),0),2) as commission_earned')
            ->value('commission_earned'));
        $defaultCurrency         = defaultCurrency();

        $chart = $this->getChartData($dateFrom, $dateTo);

        return compact('shopsCount',
            'productsCount',
            'itemsSold',
            'netSales',
            'completedOrdersCount',
            'completedOrdersPriceSum',
            'canceledOrdersCount',
            'canceledOrdersPriceSum',
            'totalEarned',
            'deliveryEarned',
            'taxEarned',
            'commissionEarned',
            'defaultCurrency',
            'chart');
    }

    public function shopsCount($dateFrom, $dateTo, $id = null)
    {
        return $this->shopsCountQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("count(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function shopsCountQuery($dateFrom, $dateTo, $shopIds = null)
    {
        return Shop::when(request('shops'), function (Builder $query) {
            $query->whereIn('id', request('shops'));
        })
            ->when($shopIds && $shopIds[0] !== null,
                fn($q) => $q->whereIn('id', $shopIds))
            ->when(request('sellers'), function (Builder $query) {
                $query->whereIn('user_id', request('sellers'));
            })
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereStatus(Shop::APPROVED);
    }

    public function itemsSold($dateFrom, $dateTo, $id = null)
    {
        return $this->itemsSoldQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(sum(quantity),2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function itemsSoldQuery($dateFrom, $dateTo, $shopIds = null)
    {
        return OrderProduct::whereHas('stock.countable',
            function ($product) use ($shopIds) {
                $product->groupBy('shop_id')
                    ->when($shopIds && $shopIds[0] !== null,
                        fn($q) => $q->whereIn('shop_id', $shopIds))
                    ->when(request('shops'), function (Builder $query) {
                        $query->whereIn('shop_id', request('shops'));
                    })
                    ->whereHas('shop', function ($shop) {
                        $shop->where('status', Shop::APPROVED)
                            ->when(request('sellers'), function (Builder $query) {
                                $query->whereIn('user_id', request('sellers'));
                            });
                    });
            })
            ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                $detail->whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('order',
                        fn($q) => $q->whereBetween('orders.created_at', [$dateFrom, $dateTo]));
            });
    }

    public function netSales($dateFrom, $dateTo, $id = null)
    {
        return $this->orderDetailQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw(OrderDetail::NETSALESSUMQUERY . " as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function ordersCount($dateFrom, $dateTo, $id = null, $status = 'completed')
    {
        return $this->ordersQuery($dateFrom, $dateTo, [$id], $status)
            ->select(
                DB::raw("COUNT(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function ordersPriceSum($dateFrom, $dateTo, $id = null, $status = 'completed')
    {
        return $this->ordersQuery($dateFrom, $dateTo, [$id], $status)
            ->select(
                DB::raw("TRUNCATE(
                    CAST(
                        sum(price)
                     as decimal(7,2))
                  ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function ordersQuery($dateFrom, $dateTo, $shopIds = null, $status = 'completed')
    {
        return Order::whereHas('orderDetails', function ($detail) use ($shopIds) {
            $detail->whereStatus(OrderDetail::DELIVERED)
                ->whereHas('products.stock.countable',
                    function ($product) use ($shopIds) {
                        $product->groupBy('shop_id')
                            ->when($shopIds && $shopIds[0] !== null,
                                fn($q) => $q->whereIn('shop_id', $shopIds))
                            ->when(request('shops'), function (Builder $query) {
                                $query->whereIn('shop_id', request('shops'));
                            })
                            ->whereHas('shop', function ($shop) {
                                $shop->where('status', Shop::APPROVED)
                                    ->when(request('sellers'), function (Builder $query) {
                                        $query->whereIn('user_id', request('sellers'));
                                    });
                            });
                    });
        })->status($status)
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo]);
    }

    public function totalEarned($dateFrom, $dateTo, $id = null)
    {
        return $this->orderDetailQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(
                    IFNULL(
                        CAST(
                            sum(price)
                         as decimal(7,2))
                     ,0)
                 ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function deliveryEarned($dateFrom, $dateTo, $id = null)
    {
        return $this->orderDetailQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(
                    IFNULL(
                        CAST(
                            sum(price)
                        as decimal(7,2))
                    ,0)
                ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function orderDetailQuery($dateFrom, $dateTo, $shopIds = null)
    {
        return OrderDetail::whereStatus(OrderDetail::DELIVERED)
            ->whereHas('products.stock.countable',
                function ($product) use ($shopIds) {
                    $product->groupBy('shop_id')
                        ->whereHas('shop', function ($shop) {
                            $shop->where('status', Shop::APPROVED)
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereIn('user_id', request('sellers'));
                                });
                        })
                        ->when($shopIds && $shopIds[0] !== null,
                            fn($q) => $q->whereIn('shop_id', $shopIds))
                        ->when(request('shops'), function (Builder $query) {
                            $query->whereIn('shop_id', request('shops'));
                        });
                })
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));
    }

    public function productsCount($dateFrom, $dateTo, $id = null)
    {
        return $this->productsCountQuery($dateFrom, $dateTo, [$id])
            ->groupBy('shop_id')
            ->select(
                DB::raw("count(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function productsCountQuery($dateFrom, $dateTo, $shopIds = null)
    {
        return Product::whereActive(true)
            ->when($shopIds && $shopIds[0] !== null,
                fn($q) => $q->whereIn('shop_id', $shopIds))
            ->when(request('shops'), function (Builder $query) {
                $query->whereIn('shop_id', request('shops'));
            })
            ->whereHas('shop', function ($shop) use ($dateFrom, $dateTo) {
                $shop->where('status', Shop::APPROVED)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->when(request('sellers'), function (Builder $query) {
                        $query->whereIn('user_id', request('sellers'));
                    });
            });
    }

    public function taxEarned($dateFrom, $dateTo, $id = null)
    {
        return $this->orderDetailQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(
                    IFNULL(
                        CAST(
                            SUM(tax)
                         as decimal(7,2))
                     ,0)
                ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function commissionEarned($dateFrom, $dateTo, $id = null)
    {
        return $this->orderDetailQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("TRUNCATE(
                    IFNULL(
                            CAST(
                                SUM(commission_fee)
                            as decimal(7,2))
                    ,0)
                ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function reportCompareCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        //return $this->reportCompare($dateFrom, $dateTo);
        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->reportCompare($dateFrom, $dateTo);
            });
    }

    public function getShopById($id)
    {
        $model = Shop::query()
            ->select(['shops.id', 'shops.uuid', 'sht.title as translation_title',])
            ->join('shop_translations as sht', function ($join) {
                $join->on('shops.id', '=', 'sht.shop_id')
                    ->where('sht.locale', app()->getLocale());
            })
            ->withTrashed()
            ->findOrFail($id);

        return CompareResource::make($model);
    }

    private function getChartData($dateFrom, $dateTo, $id = null)
    {
        switch (request('chart', 'commission_earned')) {
            case 'shops_count':
                return $this->shopsCount($dateFrom, $dateTo, $id);
            case 'products_count':
                return $this->productsCount($dateFrom, $dateTo, $id);
            case 'completed_orders_count':
                return $this->ordersCount($dateFrom, $dateTo, $id);
            case 'completed_orders_price_sum':
                return $this->ordersPriceSum($dateFrom, $dateTo);
            case 'canceled_orders_count':
                return $this->ordersCount($dateFrom, $dateTo, $id, 'canceled');
            case 'canceled_orders_price_sum':
                return $this->ordersPriceSum($dateFrom, $dateTo, $id, 'canceled');
            case 'items_sold':
                return $this->itemsSold($dateFrom, $dateTo, $id);
            case 'net_sales':
                return $this->netSales($dateFrom, $dateTo, $id);
            case 'total_earned':
                return $this->totalEarned($dateFrom, $dateTo, $id);
            case 'delivery_earned':
                return $this->deliveryEarned($dateFrom, $dateTo, $id);
            case 'tax_earned':
                return $this->taxEarned($dateFrom, $dateTo, $id);
            case 'commission_earned':
            default:
                return $this->commissionEarned($dateFrom, $dateTo, $id);
        }
    }

    public function reportCompare($dateFrom, $dateTo): array
    {
        $shopsCount              = moneyFormatter($this->shopsCountQuery($dateFrom, $dateTo, request('ids'))->count());
        $productsCount           = moneyFormatter($this->productsCountQuery($dateFrom, $dateTo, request('ids'))
            ->count());
        $itemsSold               = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('IFNULL(TRUNCATE(sum(quantity),2), 0) as quantities_sum')
            ->value('quantities_sum'));
        $netSales                = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo, request('ids'))
            ->netSalesSum()
            ->value('net_sales_sum'));
        $completedOrdersCount    = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));
        $completedOrdersPriceSum = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('TRUNCATE(sum(price),2) as orders_price_sum')
            ->value('orders_price_sum'));
        $canceledOrdersCount     = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, request('ids'), 'canceled')
            ->selectRaw('IFNULL(COUNT(id), 0) as orders_count')
            ->value('orders_count'));
        $canceledOrdersPriceSum  = moneyFormatter($this->ordersQuery($dateFrom, $dateTo, request('ids'), 'canceled')
            ->selectRaw('TRUNCATE(sum(price),2) as orders_price_sum')
            ->value('orders_price_sum'));
        $totalEarned             = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('TRUNCATE(IFNULL(SUM(price),0),2) as total_earned')
            ->value('total_earned'));
        $deliveryEarned          = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('TRUNCATE(IFNULL(SUM(delivery_fee),0),2) as delivery_earned')
            ->value('delivery_earned'));
        $taxEarned               = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('TRUNCATE(IFNULL(SUM(tax),0),2) as tax_earned')
            ->value('tax_earned'));
        $commissionEarned        = moneyFormatter($this->orderDetailQuery($dateFrom, $dateTo, request('ids'))
            ->selectRaw('TRUNCATE(IFNULL(SUM(commission_fee),0),2) as commission_earned')
            ->value('commission_earned'));
        $defaultCurrency         = defaultCurrency();

        $charts = [];
        foreach (request('ids') as $id) {
            $charts[] = [
                'translation' => $this->getShopById($id),
                'chart'       => $this->getChartData($dateFrom, $dateTo, $id),
            ];
        }

        return compact('shopsCount',
            'productsCount',
            'itemsSold',
            'netSales',
            'completedOrdersCount',
            'completedOrdersPriceSum',
            'canceledOrdersCount',
            'canceledOrdersPriceSum',
            'totalEarned',
            'deliveryEarned',
            'taxEarned',
            'commissionEarned',
            'defaultCurrency',
            'charts');
    }
}
