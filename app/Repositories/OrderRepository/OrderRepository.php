<?php

namespace App\Repositories\OrderRepository;

use App\Exports\OrdersReportExport;
use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\OrderDetailResource;
use App\Jobs\ExportJob;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\OrderRepoInterface;
use App\Services\OrderService\OrderDetailService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class OrderRepository extends CoreRepository implements OrderRepoInterface
{
    private $lang;

    /**
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }


    protected function getModelClass()
    {
        return Order::class;
    }

    public function ordersList(array $array = [])
    {
        return $this->model()->with('orderDetails.products')
            ->updatedDate($this->updatedDate)
            ->filter($array)->get();
    }

    public function ordersPaginate(int $perPage, int $userId = null, array $array = [])
    {
        return $this->model()->withCount('orderDetails')
            ->with([
                'user',
                'transaction.paymentSystem.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.orderStocks.stock.discount',
                'orderDetails.shop.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.orderStocks.stock' => function ($q) {
                    $q->select('id', 'countable_id', 'countable_type');
                },
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                }])
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where('id',  'LIKE', '%'. $array['search'] . '%')
                ->orWhere('price',  'LIKE', '%'. $array['search'] . '%')
                ->orWhere('note',  'LIKE', '%'. $array['search'] . '%');
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('orderDetails', function ($detail) use ($array) {
                    $detail->where('shop_id', $array['shop_id']);
                })->with([
                    'orderDetails' =>  function ($q) use($array) {
                        $q->where('shop_id', $array['shop_id']);
                    }]);
            })
            ->updatedDate($this->updatedDate)
            ->filter($array)
            ->when(isset($userId), function ($q) use($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')->paginate($perPage);
    }

    public function orderDetails(int $id, $shopId = null)
    {
        info('SHOP', [$shopId]);
        return $this->model()
            ->with([
                'user', 'review', 'point',
                'currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                },
                'orderDetails.deliveryType.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.deliveryAddress',
                'deliveryMan',
                'coupon',
                'userAddress',
                'delivery.translation' => fn($q) => $q->actualTranslation($this->lang),
                'orderDetails.shop.translation' => fn($q) => $q->actualTranslation($this->lang),
                'transaction.paymentSystem' => function ($q) {
                    $q->select('id', 'tag', 'active');
                },
                'transaction.paymentSystem.translation' => function ($q) {
                    $q->select('id', 'locale', 'payment_id', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.stockExtras.group.translation' => function ($q) {
                    $q->select('id', 'extra_group_id', 'locale', 'title')->actualTranslation($this->lang);
                },
                'orderDetails.orderStocks.stock.countable.translation' => function ($q) {
                    $q->select('id', 'product_id', 'locale', 'title')->actualTranslation($this->lang);
                },
            ])
            ->when(isset($shopId), function ($q) use ($shopId) {
                $q->whereHas('orderDetails', function ($detail) use ($shopId) {
                    $detail->where('shop_id', $shopId);
                })->with([
                    'orderDetails' =>  function ($q) use($shopId) {
                        $q->where('shop_id', $shopId);
                    }]);
            })
            ->find($id);
    }

    protected function orderQueryFormatter($from, $to, string $status = null): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->status($status ?? request('order_status', 'completed'))
            ->when(request('sellers'), function ($query) {
                $query->whereHas('orderDetails.products.stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('orderDetails.products.stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('orders'), fn($q) => $q->whereIn('orders.id', request('orders')))
            ->when(request('stocks'), function (Builder $query) {
                $query->whereHas('orderDetails.products', fn($q) => $q->whereIn('stock_id', request('stocks')));
            })
            ->filter(request()->all());
    }

    public function orderCountGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->orderQueryFormatter($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("count(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function netSalesSum($dateFrom, $dateTo)
    {
        return $this->netSalesQuery($dateFrom, $dateTo)->netSalesSum()->value('net_sales_sum');
    }

    public function ordersCount($dateFrom, $dateTo): int
    {
        return $this->orderQueryFormatter($dateFrom, $dateTo)->count();
    }

    /**
     * @param array $filter
     * @return array
     */
    public function orderByStatusStatistics(array $filter = []): array
    {
        $delivered = OrderDetail::DELIVERED;
        $canceled  = OrderDetail::CANCELED;
        $new       = OrderDetail::NEW;
        $accepted  = OrderDetail::ACCEPTED;
        $ready     = OrderDetail::READY;
        $onAWay    = OrderDetail::ON_A_WAY;
        $date      = date('Y-m-d 00:00:01');

        $result    = [
            'count'                 => 0,
            'total_price'           => 0,
            'delivered'             => 0,
            'cancel'                => 0,
            'new'                   => 0,
            'accepted'              => 0,
            'ready'                 => 0,
            'on_a_way'              => 0,
            'today_count'           => 0,
            'total_delivered_price' => 0,
        ];

//        $filter['date_from'] = date('Y-m-d H:i:s', strtotime('-1 minute'));

        OrderDetail::filter($filter)
            ->select(['id', 'price', 'status', 'created_at'])
            ->chunkMap(function (OrderDetail $order) use (&$result, $date, $delivered, $canceled, $new, $accepted, $ready, $onAWay) {

                $result['count'] += 1;
                $result['total_price'] += $order->price;
                if ($order->status === OrderDetail::DELIVERED) {
                    $result['total_delivered_price'] += $order->price;
                }

                if ($order->created_at >= $date) {
                    $result['today_count'] += 1;
                }

                switch ($order->status) {
                    case $delivered:
                        $result[$delivered] += 1;
                        break;
                    case $canceled:
                        $result['cancel'] += 1;
                        break;
                    case $new:
                        $result[$new] += 1;
                        break;
                    case $accepted:
                        $result[$accepted] += 1;
                        break;
                    case $ready:
                        $result[$ready] += 1;
                        break;
                    case $onAWay:
                        $result[$onAWay] += 1;
                        break;
                }

                return true;
            });

        $progress = data_get($result, 'new', 0) + data_get($result, 'accepted', 0) +
            data_get($result, 'ready', 0) + data_get($result, 'on_a_way', 0);

        return [
            'progress_orders_count'     => $progress,
            'delivered_orders_count'    => data_get($result, 'delivered'),
            'total_delivered_price'     => data_get($result, 'total_delivered_price'),
            'cancel_orders_count'       => data_get($result, 'cancel'),
            'new_orders_count'          => data_get($result, 'new'),
            'accepted_orders_count'     => data_get($result, 'accepted'),
            'ready_orders_count'        => data_get($result, 'ready'),
            'on_a_way_orders_count'     => data_get($result, 'on_a_way'),
            'orders_count'              => data_get($result, 'count'),
            'total_price'               => data_get($result, 'total_price'),
            'today_count'               => data_get($result, 'today_count'),
        ];
    }

    public function orderReportChartCache()
    {
        [$dateFrom, $dateTo] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), 3600,
            function () use ($dateFrom, $dateTo) {
                return $this->ordersReportChart($dateFrom, $dateTo);
            });
    }

    public function ordersReportChart($dateFrom, $dateTo)
    {
        $netSalesSum = $this->netSalesQuery($dateFrom, $dateTo)->netSalesSum()->value('net_sales_sum');
        $ordersCount = $this->orderQueryFormatter($dateFrom, $dateTo)->count();
        $itemsSold   = $this->itemsSoldQuery($dateFrom, $dateTo)->sum('quantity');

        $netSalesAvg     = moneyFormatter($netSalesSum / ($ordersCount ? : 1));
        $itemsSoldAvg    = moneyFormatter($itemsSold / ($ordersCount ? : 1));
        $netSalesSum     = moneyFormatter($netSalesSum);
        $ordersCount     = moneyFormatter($ordersCount);
        $defaultCurrency = defaultCurrency();

        switch (request('chart', 'avg_items_sold')) {
            case 'orders':
                $chart = $this->orderCountGroupByTime($dateFrom, $dateTo);
                break;
            case 'net_sales':
                $chart = $this->netSalesSumGroupByTime($dateFrom, $dateTo);
                break;
            case 'avg_order_value':
                $chart = $this->netSalesAvgGroupByTime($dateFrom, $dateTo);
                break;
            default:
                $chart = $this->itemsSoldAvgGroupByTime($dateFrom, $dateTo);
                break;
        }

        return compact('netSalesSum', 'ordersCount', 'itemsSoldAvg', 'netSalesAvg', 'chart', 'defaultCurrency');
    }

    public function ordersReportPaginateQuery($dateFrom, $dateTo, ?int $perPage = 15)
    {
        return Order::query()
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->select(['orders.id',
                'orders.user_id',
                'orders.created_at',
                'users.firstname as user_firstname',
                'users.lastname as user_lastname',
                'users.active as user_active',
                'status' => DB::raw("CASE WHEN exists(select * from order_details where order_details.order_id=orders.id and order_details.status in ('new', 'accepted', 'ready', 'on_a_way', 'paid')) THEN 'open' WHEN exists(select * from order_details where order_details.order_id=orders.id and order_details.status in ('delivered')) THEN 'completed' ELSE 'canceled' END as 'status'"),
            ])
            ->addSelect([//shows how many items were sold in this order
                'item_sold' => OrderProduct::query()
                    ->whereHas('detail', function ($detail) {
                        $detail->whereHas('order', function ($order) {
                            $order->status(request('order_status', 'completed'));
                        })
                            ->when(request('order_status', 'completed') !== 'canceled',
                                fn(Builder $q) => $q->whereNotIn('status', ['canceled']))
                            ->when(request('orders'), fn($q) => $q->whereIn('order_id', request('orders')))
                            ->whereColumn('order_id', 'orders.id');
                    })
                    ->when(request('shops'), fn($q) => $q->whereHas('stock.countable',
                        fn($p) => $p->whereIn('shop_id', request('shops'))))
                    ->when(request('stocks'), function (Builder $query) {
                        $query->whereIn('stock_id', request('stocks'));
                    })
                    ->when(request('sellers'), function (Builder $query) {
                        $query->whereHas('stock.countable.shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                    ->selectRaw('IFNULL(sum(quantity), 0) as sum_quantity'),
                'net_sales' => OrderDetail::query()
                    ->whereHas('order', function ($order) {
                        $order->status(request('order_status', 'completed'));
                    })
                    ->when(request('order_status', 'completed') !== 'canceled',
                        fn(Builder $q) => $q->whereNotIn('status', ['canceled']))
                    ->when(request('orders'), fn($q) => $q->whereIn('order_id', request('orders')))
                    ->when(request('stocks'), function (Builder $query) {
                        $query->whereHas('products', fn($q) => $q->whereIn('stock_id', request('stocks')));
                    })
                    ->when(request('shops'), fn($q) => $q->whereHas('products.stock.countable',
                        fn($p) => $p->whereIn('shop_id', request('shops'))))
                    ->when(request('sellers'), function ($query) {
                        $query->whereHas('products.stock.countable.shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                    ->whereColumn('order_id', 'orders.id')
                    ->netSalesSum(),
            ])
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->with([
                'orderDetails'                                      => function ($detail) {
                    $detail->when(request('sellers'), function ($query) {
                        $query->whereHas('products.stock.countable.shop',
                            fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                        ->when(request('shops'), function ($query) {
                            $query->whereHas('products.stock.countable',
                                fn($q) => $q->whereIn('shop_id', request('shops')));
                        })->select('id', 'status', 'order_id');
                },
                'orderDetails.products'                             => function ($query) {
                    $query->when(request('stocks'), function ($query) {
                        $query->whereIn('stock_id', request('stocks'));
                    })
                        ->when(request('sellers'), function ($query) {
                            $query->whereHas('stock.countable.shop',
                                fn($q) => $q->whereIn('user_id', request('sellers')));
                        })
                        ->when(request('shops'), function ($query) {
                            $query->whereHas('stock.countable',
                                fn($q) => $q->whereIn('shop_id', request('shops')));
                        })
                        ->select('id', 'order_detail_id', 'stock_id');
                },

                'orderDetails.products.stock:id,countable_id,countable_type',
                'orderDetails.products.stock.countable'             => function ($product) {
                    $product->when(request('sellers'), function (Builder $query) {
                        $query->whereHas('shop', fn($q) => $q->whereIn('user_id', request('sellers')));
                    })
                        ->when(request('shops'), fn($q) => $q->whereIn('shop_id', request('shops')))
                        ->select('id', 'uuid', 'active', 'shop_id');
                },
                //'orderDetails.products.stock.countable.shop:id,uuid,user_id',
                //'orderDetails.products.stock.countable.shop.seller:id,firstname,lastname',
                //'orderDetails.products.stock.countable.shop.translations' =>
                //    fn($q) => $q->where('locale', $this->lang)->select('id', 'shop_id', 'locale', 'title'),
                'orderDetails.products.stock.countable.translation' =>
                    fn($q) => $q->where('locale', $this->lang)->select('id', 'product_id', 'locale', 'title'),
            ])
            ->when(request('orders'), fn($q) => $q->whereIn('orders.id', request('orders')))
            ->when(request('shops'), fn($q) => $q->whereHas('orderDetails.products.stock.countable',
                fn($p) => $p->whereIn('shop_id', request('shops'))
            ))
            ->when(request('stocks'), function (Builder $query) {
                $query->whereHas('orderDetails.products', fn($q) => $q->whereIn('stock_id', request('stocks')));
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereHas('orderDetails.products.stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->orderBy(request('column', 'id'), request('sort', 'desc'))
            ->status(request('order_status', 'completed'))
            ->filter(request()->all())
            ->when($perPage,
                fn($q) => $q->paginate($perPage),
                fn($q) => $q->get());
    }

    public function ordersReportPaginate($perPage)
    {
        [$dateFrom, $dateTo] = dateFromToFormatter();
        $perPage = request('export') === 'excel' ? null : $perPage;

        $data = Cache::remember(md5(url()->current() . '?' . http_build_query(request()->except("page"))), 3600,
            function () use ($dateFrom, $dateTo, $perPage) {
                return $this->ordersReportPaginateQuery($dateFrom, $dateTo, $perPage);
            });

        if (request('export') === 'excel') {
            $name = 'orders-report-products-' . Str::random(8);

//            Excel::store(new OrdersReportExport($query->get()), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, OrdersReportExport::class);

            return [
                'path'      => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link'      => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $data;
    }

    protected function itemsSoldQuery($from, $to, string $status = null): Builder
    {
        return OrderProduct::query()
            ->whereHas('detail', function (Builder $detail) use ($from, $to, $status) {
                $detail
                    ->groupBy('order_id')
                    ->when(request('orders'), fn($q) => $q->whereIn('order_id', request('orders')))
                    ->whereHas('order', function ($query) use ($from, $to, $status) {
                        $query->whereBetween('created_at', [$from, $to])
                            ->status($status ?? request('order_status', 'completed'));
                    });
            })
            ->when(request('sellers'), function ($query) {
                $query->whereHas('stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('stocks'), function (Builder $query) {
                $query->whereIn('stock_id', request('stocks'));
            });
    }

    protected function itemsSoldGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->itemsSoldQuery($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("SUM(quantity) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    protected function itemsSoldAvgGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        $orderProducts = OrderProduct::query()
            ->whereHas('detail', function (Builder $detail) use ($dateFrom, $dateTo, $status) {
                $detail
                    ->groupBy('order_id')
                    ->when(request('orders'), fn($q) => $q->whereIn('order_id', request('orders')))
                    ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $status) {
                        $query->whereBetween('created_at', [$dateFrom, $dateTo])
                            ->status($status ?? request('order_status', 'completed'));
                    });
            })
            ->when(request('sellers'), function ($query) {
                $query->whereHas('stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('stocks'), function (Builder $query) {
                $query->whereIn('stock_id', request('stocks'));
            })
            ->join('order_details as od', function ($join) {
                $join->on('order_products.order_detail_id', '=', 'od.id')
                    ->join('orders', function (JoinClause $join) {
                        $join->on('od.order_id', '=', 'orders.id');
                    });
            })
            ->groupBy('od.order_id')
            ->select(
            //DB::raw("(DATE_FORMAT(orders.created_at, " . (request('by_time') == 'year' ? "'%Y" : (request('by_time') == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
            //DB::raw("IFNULL(TRUNCATE(SUM(order_details.price - IFNULL(order_details.tax ,0)- IFNULL(order_details.commission_fee ,0)),2), 0) as result"),
                DB::raw("SUM(order_products.quantity) as result"),
                DB::raw("(DATE_FORMAT(orders.created_at, " . (request('by_time') == 'year' ? "'%Y" : (request('by_time') == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $orders = $this->orderQueryFormatter($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("IFNULL(count(id), 1) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');
        $result = [];
        foreach ($orderProducts as $time => $detail) {
            $result[] = [
                'time'   => $time,
                'result' => number_format($detail / $orders[$time], 2, '.', ''),
            ];
        }

        return $result;
    }

    protected function netSalesQuery($dateFrom, $dateTo, string $status = null)
    {
        return OrderDetail::query()
            ->when(request('orders'), fn($q) => $q->whereIn('order_id', request('orders')))
            ->when(request('sellers'), function ($query) {
                $query->whereHas('products.stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('products.stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('stocks'), function (Builder $query) {
                $query->whereHas('products', fn($q) => $q->whereIn('stock_id', request('stocks')));
            })
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $status) {
                $query->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status($status ?? request('order_status', 'completed'))
                    ->filter(request()->all());
            });
    }

    public function netSalesSumGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, $status)
            ->select(
                orderSelectDateFormat(request('by_time')),
                DB::raw(OrderDetail::NETSALESSUMQUERY . " as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    protected function netSalesAvgGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        $orderDetails = OrderDetail::query()
            ->when(request('orders'), fn($q) => $q->whereIn('order_details.order_id', request('orders')))
            ->when(request('sellers'), function ($query) {
                $query->whereHas('products.stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('products.stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->when(request('stocks'), function (Builder $query) {
                $query->whereHas('products', fn($q) => $q->whereIn('stock_id', request('stocks')));
            })
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo, $status) {
                $query->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status($status ?? request('order_status', 'completed'))
                    ->filter(request()->all());
            })->join('orders', function (JoinClause $join) {
                $join->on('order_details.order_id', '=', 'orders.id');
            })
            ->groupBy('order_details.order_id')
            ->select(
                DB::raw("(DATE_FORMAT(orders.created_at, " . (request('by_time') == 'year' ? "'%Y" : (request('by_time') == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
                DB::raw("IFNULL(TRUNCATE(SUM(order_details.price - IFNULL(order_details.tax ,0)- IFNULL(order_details.commission_fee ,0)),2), 0) as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $orders = $this->orderQueryFormatter($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("IFNULL(count(id), 1) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');
        $result = [];
        foreach ($orderDetails as $time => $detail) {
            $result[] = [
                'time'   => $time,
                'result' => number_format($detail / $orders[$time], 2, '.', ''),
            ];
        }

        return $result;
    }
}
