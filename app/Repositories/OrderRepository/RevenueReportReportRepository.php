<?php

namespace App\Repositories\OrderRepository;

use App\Exports\RevenueReportExport;
use App\Jobs\ExportJob;
use App\Models\OrderDetail;
use App\Repositories\Interfaces\RevenueReportRepoInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\{Cache, DB, URL};
use Excel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RevenueReportReportRepository extends OrderRepository implements RevenueReportRepoInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function reportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers',[])) . implode('.', request('shops',[]))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->reportChart($dateFrom, $dateTo);
            });
    }

    public function reportChart($dateFrom, $dateTo)
    {
        $totalPrice        = moneyFormatter($this->orderQueryFormatter($dateFrom, $dateTo, 'completed')
            ->sum('price'));
        $netSalesSum       = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->netSalesSum()
            ->value('net_sales_sum'));
        $canceledOrders    = moneyFormatter($this->orderQueryFormatter($dateFrom, $dateTo, 'canceled')
            ->sum('price'));
        $taxTotal          = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, 'completed')->sum('tax'));
        $totalShippingFree = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->sum('delivery_fee'));
        $defaultCurrency   = defaultCurrency();

        switch (request('chart', 'canceled_orders_count')) {
            case 'total_price':
                $chart = $this->totalPriceGroupByTime($dateFrom, $dateTo, 'completed');
                break;
            case 'net_sales':
                $chart = $this->netSalesSumGroupByTime($dateFrom, $dateTo, 'completed');
                break;
            case 'tax_total':
                $chart = $this->taxTotalGroupByTime($dateFrom, $dateTo, 'completed');
                break;
            case 'total_shipping_free':
                $chart = $this->deliveryFreeSumGroupByTime($dateFrom, $dateTo, 'completed');
                break;
            case 'canceled_orders_count':
            default:
                $chart = $this->ordersCount($dateFrom, $dateTo, 'canceled');
                break;
        }

        return compact('totalPrice',
            'netSalesSum',
            'canceledOrders',
            'taxTotal',
            'totalShippingFree',
            'defaultCurrency',
            'chart');
    }

    protected function totalPriceGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->orderQueryFormatter($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("TRUNCATE( CAST( SUM(price) as decimal(7,2)) ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    protected function taxTotalGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, $status)
            ->select(
                DB::raw("TRUNCATE( CAST( SUM(tax) as decimal(7,2)) ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    protected function deliveryFreeSumGroupByTime($dateFrom, $dateTo, string $status = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, $status)
            ->select(
                orderSelectDateFormat(request('by_time')),
                DB::raw("TRUNCATE( CAST( SUM(delivery_fee) as decimal(7,2)) ,2) as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function reportPaginate($perPage)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();
        $perPage = request('export') === 'excel' ? null : $perPage;
        $data    = Cache::remember(md5(url()->current() . '?' . http_build_query(request()->except("page")) . implode('.', request('sellers',[])) . implode('.', request('shops',[]))), $ttl,
            function () use ($dateFrom, $dateTo, $perPage) {
                return $this->reportPaginateQuery($dateFrom, $dateTo, $perPage);
            });

        if (request('export') === 'excel') {
            $name = 'categories-report-' . Str::random(8);

            //Excel::store(new RevenueReportExport($data), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, RevenueReportExport::class);

            return [
                'path'      => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link'      => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $this->paginate($data, $perPage, request('page'));
    }

    public function reportPaginateQuery($dateFrom, $dateTo, ?int $perPage = 15)
    {
        $dates  = [];
        $period = CarbonPeriod::create($dateFrom, $dateTo);

        foreach ($period as $date) {
            $dates [] = $date->format(request('by_time') == 'year' ? 'Y' : (request('by_time') == 'month' ? 'Y-m' : 'Y-m-d'));
        }
        $dates = array_unique($dates);

        $netSales = OrderDetail::query()
            ->when(request('orders'), fn($q) => $q->whereIn('order_details.order_id', request('orders')))
            ->whereHas('order', function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('orders.created_at', [$dateFrom, $dateTo])
                    ->status('completed')
                    ->filter(request()->all());
            })->join('orders', function (JoinClause $join) {
                $join->on('order_details.order_id', '=', 'orders.id');
            })
            ->when(request('sellers'), function ($query) {
                $query->whereHas('products.stock.countable.shop',
                    fn($q) => $q->whereIn('user_id', request('sellers')));
            })
            ->when(request('shops'), function ($query) {
                $query->whereHas('products.stock.countable',
                    fn($q) => $q->whereIn('shop_id', request('shops')));
            })
            ->groupBy('order_details.order_id')
            ->select(
                DB::raw("(DATE_FORMAT(orders.created_at, " . (request('by_time') == 'year' ? "'%Y" : (request('by_time') == 'month' ? "'%Y-%m" : "'%Y-%m-%d")) . "')) as time"),
                DB::raw("IFNULL(TRUNCATE(SUM(order_details.price - IFNULL(order_details.tax ,0)- IFNULL(order_details.commission_fee ,0)),2), 0) as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $completedOrders = $this->orderQueryFormatter($dateFrom, $dateTo, 'completed')
            ->select(
                DB::raw("ifnull(count(*),0) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $totalPrice = $this->orderQueryFormatter($dateFrom, $dateTo, 'completed')
            ->select(
                DB::raw("TRUNCATE( CAST( SUM(price) as decimal(7,2)) ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $canceledOrders = $this->orderQueryFormatter($dateFrom, $dateTo, 'cancelled')
            ->select(
                DB::raw("TRUNCATE( CAST( SUM(price) as decimal(7,2)) ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $taxTotal = $this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->select(
                DB::raw("TRUNCATE( CAST( SUM(tax) as decimal(7,2)) ,2) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->groupBy('order_details.order_id')
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $totalShippingFree = $this->netSalesQuery($dateFrom, $dateTo, 'completed')
            ->groupBy('order_details.order_id')
            ->select(
                orderSelectDateFormat(request('by_time')),
                DB::raw("TRUNCATE( CAST( SUM(delivery_fee) as decimal(7,2)) ,2) as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->pluck('result', 'time');

        $result = collect();
        foreach ($dates as $date) {
            /**
             * to remove all 0 column
             */
            if (empty($completedOrders[$date]) && empty($canceledOrders[$date]) && empty($netSales[$date]) &&
                empty($taxTotal[$date]) && empty($totalShippingFree[$date]) && empty($totalPrice[$date])) {
                continue;
            }

            $result->add([
                'date'        => $date,
                'orders'      => $completedOrders[$date] ?? 0,
                'returns'     => $canceledOrders[$date] ?? 0,
                'net_sales'   => $netSales[$date] ?? 0,
                'taxes'       => $taxTotal[$date] ?? 0,
                'shipping'    => $totalShippingFree[$date] ?? 0,
                'total_sales' => $totalPrice[$date] ?? 0,
            ]);
        }

        if (in_array(request('sort', 'DESC'), ['ASC', 'asc'])) {
            $result = $result->sortBy(request('column', 'date'))->values();
        } else {
            $result = $result->sortByDesc(request('column', 'date'))->values();
        }

        return $result;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function paginate(Collection $items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ? : (Paginator::resolveCurrentPage() ? : 1);

        //$items = $items instanceof Collection ? $items : Collection::make($items);

        return new LengthAwarePaginator(array_values($items->forPage($page, $perPage)->toArray()), $items->count(),
            $perPage,
            $page, $options);
    }
}
