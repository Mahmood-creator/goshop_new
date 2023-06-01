<?php

namespace App\Repositories\CategoryRepository;

use App\Exports\CategoriesReportExport;
use App\Http\Resources\CompareResource;
use App\Jobs\ExportJob;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\CategoryRepoInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class CategoryRepository extends CoreRepository implements CategoryRepoInterface
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
        return Category::class;
    }

    public function mostSoldProductCategories(array $array)
    {
        $stockIds = OrderProduct::with('stock.countable:id')
            ->whereHas('detail',function ($q){
                $q->where('status',OrderDetail::DELIVERED);
            })
            ->select('stock_id',DB::raw('COUNT(quantity) AS `COUNT`'))
            ->groupBy('stock_id')
            ->orderBy('count', 'DESC')
            ->pluck('stock_id');

        return Category::with(['translation' => function ($q){
            $q->where('locale',$this->lang);
        }])->whereHas('products.stocks',function ($q) use ($stockIds){
            $q->whereIn('id',$stockIds);
        })->select('id')->paginate($array['perPage'] ?? 15);

    }

    /**
     * Get Parent, only categories where parent_id == 0
     */
    public function parentCategories($perPage, $active = null, array $array = [])
    {
        return $this->model()->updatedDate($this->updatedDate)
            ->whereHas('translations', function ($q) use ($array) {
                $q->when(isset($array['search']), function ($q) use ($array) {
                    $q->where('title', 'LIKE', '%' . $array['search'] . '%')
                        ->select('id', 'category_id', 'locale', 'title');
                });

            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('children.children.products', function ($query) use ($array) {
                    $query->where('shop_id', $array['shop_id']);
                });
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->whereHas('children.products', function ($query) use ($array) {
                    $query->where('shop_id', $array['shop_id']);
                });
            })
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->filter($array)
            ->where('parent_id', 0)
            ->with([
                'translation' => fn($q) => $q->select('id', 'locale', 'title', 'category_id')
                    ->where('locale', $this->lang),
                'children',
                'children.translation' => fn($q) => $q->where('locale', $this->lang),
                'children.children',
                'children.children.translation' => fn($q) => $q->where('locale', $this->lang),
            ])
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })->orderByDesc('id')->paginate($perPage);
    }

    /**
     * Get categories with pagination
     */
    public function categoriesPaginate($perPage = 15, $active = null, $array = [])
    {
        return $this->model()->updatedDate($this->updatedDate)
            ->with([
                'translation' => fn($q) => $q->select('id', 'locale', 'title', 'category_id')
                    ->actualTranslation($this->lang),
                'children.translation' => fn($q) => $q->actualTranslation($this->lang)
            ])
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get all categories list
     */
    public function categoriesList($array = [])
    {
        return $this->model()->updatedDate($this->updatedDate)->with([
            'parent.translation' => fn($q) => $q->actualTranslation($this->lang),
            'translation' => fn($q) => $q->select('id', 'locale', 'title', 'category_id')
                ->where('locale', $this->lang)
        ])->orderByDesc('id')->get();
    }

    /**
     * Get one category by Identification number
     */
    public function categoryDetails(int $id)
    {
        return $this->model()->with([
            'parent.translation' => fn($q) => $q->actualTranslation($this->lang),
            'translation' => fn($q) => $q->actualTranslation($this->lang)
        ])->find($id);
    }

    /**
     * Get one category by slug
     */
    public function categoryByUuid($uuid)
    {
        return $this->model()->where('uuid', $uuid)->withCount('products')
            ->with([
                'translation' => fn($q) => $q->actualTranslation($this->lang),
                'children.translation' => fn($q) => $q->actualTranslation($this->lang)
            ])->first();
    }

    public function categoriesSearch(string $search, $active = null)
    {
        return $this->model()->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)
        ])
            ->where(function ($query) use ($search) {
                $query->where('keywords', 'LIKE', '%' . $search . '%');
            })
            ->whereHas('translations', function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->select('id', 'category_id', 'locale', 'title');
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })
            ->orderByDesc('id')
            ->latest()->take(50)->get();
    }

    //REPORT

    public function reportPagination($perPage)
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        $perPage = request('export') === 'excel' ? null : $perPage;

        $data = Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo, $perPage) {
                return $this->reportPaginationQuery($dateFrom, $dateTo, $perPage);
            });

        if (request('export') === 'excel') {
            $name = 'categories-report-' . Str::random(8);

            //Excel::store(new CategoriesReportExport($data), "export/$name.xlsx", 'public');
            ExportJob::dispatchAfterResponse("export/$name.xlsx", $data, CategoriesReportExport::class);

            return [
                'path' => 'public/export',
                'file_name' => "export/$name.xlsx",
                'link' => URL::to("storage/export/$name.xlsx"),
            ];
        }

        return $data;
    }

    public function reportPaginationQuery($dateFrom, $dateTo, ?int $perPage = 15)
    {
        $search = (string)request('search');

        return Category::select(
            ['categories.id',
                'categories.uuid',
                'categories.keywords',
                'categories.parent_id',
                'cat_t.title as translation_title'])
            ->addSelect([
                'items_sold' => OrderProduct::whereHas('stock', function (Builder $stock) {
                    $stock->where('countable_type', Product::class)
                        ->whereHas('countable', function (Builder $product) {
                            $product->whereColumn('category_id', 'categories.id')
                                ->when(request('shops'), function (Builder $query) {
                                    $query->whereIn('shop_id', request('shops'));
                                })
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereHas('shop', function ($query) {
                                        $query->whereIn('user_id', request('sellers'));
                                    });
                                });
                        });
                })
                    ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereStatus(OrderDetail::DELIVERED)
                            ->whereHas('order', function (Builder $order) use ($dateFrom, $dateTo) {
                                $order->whereDate('created_at', '>=', $dateFrom)
                                    ->whereDate('created_at', '<=', $dateTo);
                            });
                    })
                    ->selectRaw('IFNULL(sum(quantity), 0)  as sum_quantity'),
                'net_sales' => OrderDetail::query()
                    ->whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('products.stock',
                        function ($stock) {
                            $stock->where('countable_type', Product::class)
                                ->whereHas('countable', function (Builder $product) {
                                    $product->whereColumn('category_id', 'categories.id')
                                        ->when(request('shops'), function (Builder $query) {
                                            $query->whereIn('shop_id', request('shops'));
                                        })
                                        ->when(request('sellers'), function (Builder $query) {
                                            $query->whereHas('shop', function ($query) {
                                                $query->whereIn('user_id', request('sellers'));
                                            });
                                        });;
                                });
                        })
                    ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
                    ->netSalesSum(),
                'orders_count' => Order::whereHas('orderDetails', function ($detail) {
                    $detail
                        //->whereStatus(OrderDetail::DELIVERED)
                        ->whereHas('products.stock',
                            function ($stock) {
                                $stock->where('countable_type', Product::class)
                                    ->whereHas('countable', function (Builder $product) {
                                        $product->whereColumn('category_id', 'categories.id')
                                            ->when(request('shops'), function (Builder $query) {
                                                $query->whereIn('shop_id', request('shops'));
                                            })
                                            ->when(request('sellers'), function (Builder $query) {
                                                $query->whereHas('shop', function ($query) {
                                                    $query->whereIn('user_id', request('sellers'));
                                                });
                                            });;
                                    });
                            });
                })
                    //->status('completed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->selectRaw('IFNULL(COUNT(id), 0)'),
            ])
            ->whereActive(1)
            ->join('category_translations as cat_t', function ($join) {
                $join->on('categories.id', '=', 'cat_t.category_id')
                    ->where('cat_t.locale', app()->getLocale());
            })
            ->when($search, function ($query) use ($search) {
                $search = rtrim($search, " \t.");
                $query->where(function ($query) use ($search) {
                    $query->where('categories.keywords', 'LIKE', "%$search%")
                        ->orWhere('cat_t.title', 'LIKE', "%$search%")
                        ->orWhereHas('parent', function ($query) use ($search) {
                            $query->where('keywords', 'LIKE', "%$search%")
                                ->orWhereHas('translations', function ($q) use ($search) {
                                    $q->where('title', 'LIKE', "%$search%");
                                })->orWhereHas('parent', function ($query) use ($search) {
                                    $query->where('keywords', 'LIKE', "%$search%")
                                        ->orWhereHas('translations', function ($q) use ($search) {
                                            $q->where('title', 'LIKE', "%$search%");
                                        });
                                });
                        });
                });
            })
            ->when(request('shops'), function (Builder $query) {
                $query->whereHas('products', function ($query) {
                    $query->whereIn('shop_id', request('shops'));
                });
            })
            ->when(request('sellers'), function (Builder $query) {
                $query->whereHas('products.shop', function ($query) {
                    $query->whereIn('user_id', request('sellers'));
                });
            })
            ->when(request('categories'), function ($query) {
                $query->whereIn('categories.id', request('categories'));
            })
            ->whereHas('products.stocksWithTrashed', function (Builder $stock) use ($dateFrom, $dateTo) {
                $stock->withTrashed()->where('countable_type', Product::class)
                    ->whereHas('orderProducts.detail', function ($detail) use ($dateFrom, $dateTo) {
                        $detail->whereStatus(OrderDetail::DELIVERED)
                            ->whereHas('order', function (Builder $order) use ($dateFrom, $dateTo) {
                                $order->whereDate('created_at', '>=', $dateFrom)
                                    ->whereDate('created_at', '<=', $dateTo);
                            });
                    });
            })
            ->with(['parenRecursive'])
            ->withCount(['products'])
            ->orderBy(request('column', 'id'), request('sort', 'desc'))
            ->when($perPage,
                fn($q) => $q->paginate($perPage),
                fn($q) => $q->get());
    }

    public function categoriesReportChartCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->reportChart($dateFrom, $dateTo);
            });
    }

    public function reportChart($dateFrom, $dateTo)
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo)->sum('quantity'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo)->netSalesSum()->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo)->count());
        switch (request('chart', 'commission_earned')) {
            case 'orders_count':
                $chart = $this->ordersCount($dateFrom, $dateTo);
                break;
            case 'net_sales':
                $chart = $this->netSales($dateFrom, $dateTo);
                break;
            case 'items_sold':
            default:
                $chart = $this->itemsSold($dateFrom, $dateTo);
        }

        $defaultCurrency = defaultCurrency();

        return compact('itemsSold', 'netSales', 'ordersCount', 'chart', 'defaultCurrency');
    }

    public function itemsSold($dateFrom, $dateTo, $id = null)
    {
        return $this->itemsSoldQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("sum(quantity) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function itemsSoldQuery($dateFrom, $dateTo, $categoriesIds = null)
    {
        return OrderProduct::whereHas('stock', function (Builder $stock) use ($categoriesIds) {
            $stock->where('countable_type', Product::class)
                ->whereHas('countable', function (Builder $product) use ($categoriesIds) {
                    $product->groupBy('category_id')
                        ->when($categoriesIds && $categoriesIds[0] !== null,
                            fn($q) => $q->whereIn('category_id', $categoriesIds))
                        ->when(request('shops'), function (Builder $query) {
                            $query->whereIn('shop_id', request('shops'));
                        })
                        ->when(request('sellers'), function (Builder $query) {
                            $query->whereHas('shop', function ($query) {
                                $query->whereIn('user_id', request('sellers'));
                            });
                        })
                        ->when(request('categories'),
                            fn($q) => $q->whereIn('category_id', request('categories')))
                        ->whereHas('category', fn($c) => $c->whereActive(1));
                });
        })
            ->whereHas('detail', function ($detail) use ($dateFrom, $dateTo) {
                $detail->whereStatus(OrderDetail::DELIVERED)
                    ->whereHas('order', function (Builder $order) use ($dateFrom, $dateTo) {
                        $order->whereDate('created_at', '>=', $dateFrom)
                            ->whereDate('created_at', '<=', $dateTo);
                    });
            });
    }

    public function netSales($dateFrom, $dateTo, $id = null)
    {
        return $this->netSalesQuery($dateFrom, $dateTo, [$id])
            ->select(
                orderSelectDateFormat(request('by_time')),
                DB::raw(OrderDetail::NETSALESSUMQUERY . " as result")
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function netSalesQuery($dateFrom, $dateTo, $categoriesIds = null)
    {
        return OrderDetail::query()
            ->whereStatus(OrderDetail::DELIVERED)
            ->whereHas('products.stock',
                function ($stock) use ($categoriesIds) {
                    $stock->where('countable_type', Product::class)
                        ->whereHas('countable', function (Builder $product) use ($categoriesIds) {
                            $product->groupBy('category_id')
                                ->when($categoriesIds && $categoriesIds[0] !== null,
                                    fn($q) => $q->whereIn('category_id', $categoriesIds))
                                ->when(request('categories'),
                                    fn($q) => $q->whereIn('category_id', request('categories')))
                                ->when(request('shops'), function (Builder $query) {
                                    $query->whereIn('shop_id', request('shops'));
                                })
                                ->when(request('sellers'), function (Builder $query) {
                                    $query->whereHas('shop', function ($query) {
                                        $query->whereIn('user_id', request('sellers'));
                                    });
                                })
                                ->whereHas('category', fn($c) => $c->whereActive(1));
                        });
                })
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]));
    }

    public function ordersCount($dateFrom, $dateTo, $id = null)
    {
        return $this->ordersCountQuery($dateFrom, $dateTo, [$id])
            ->select(
                DB::raw("COUNT(id) as result"),
                orderSelectDateFormat(request('by_time'))
            )
            ->oldest('time')
            ->groupBy(DB::raw("time"))
            ->get();
    }

    public function ordersCountQuery($dateFrom, $dateTo, $categoriesIds = null)
    {
        return Order::whereHas('orderDetails', function ($detail) use ($categoriesIds) {
            $detail
                //->whereStatus(OrderDetail::DELIVERED)
                ->whereHas('products.stock',
                    function ($stock) use ($categoriesIds) {
                        $stock->where('countable_type', Product::class)
                            ->whereHas('countable', function (Builder $product) use ($categoriesIds) {
                                $product->groupBy('category_id')
                                    ->when($categoriesIds && $categoriesIds[0] !== null,
                                        fn($q) => $q->whereIn('category_id', $categoriesIds))
                                    ->when(request('categories'),
                                        fn($q) => $q->whereIn('category_id', request('categories')))
                                    ->when(request('shops'), function (Builder $query) {
                                        $query->whereIn('shop_id', request('shops'));
                                    })
                                    ->when(request('sellers'), function (Builder $query) {
                                        $query->whereHas('shop', function ($query) {
                                            $query->whereIn('user_id', request('sellers'));
                                        });
                                    })
                                    ->whereHas('category', fn($c) => $c->whereActive(1));
                            });
                    });
        })
            ->status('completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
    }

    public function reportCompareCache()
    {
        [$dateFrom, $dateTo, $ttl] = dateFromToFormatter();

        return Cache::remember(md5(request()->fullUrl() . implode('.', request('sellers', [])) . implode('.', request('shops', []))), $ttl,
            function () use ($dateFrom, $dateTo) {
                return $this->reportCompare($dateFrom, $dateTo);
            });
    }

    public function getCategoryById($id)
    {
        $model = $this->model()
            ->select(['categories.id as id', 'categories.uuid as uuid', 'ct.title as translation_title'])
            ->where('categories.active', 1)
            ->where('categories.id', $id)
            ->join('category_translations as ct', function ($join) {
                $join->on('categories.id', '=', 'ct.category_id')
                    ->where('ct.locale', app()->getLocale());
            })
            ->first();

        return CompareResource::make($model);
    }

    public function reportCompare($dateFrom, $dateTo): array
    {
        $itemsSold = moneyFormatter($this->itemsSoldQuery($dateFrom, $dateTo, request('ids'))->sum('quantity'));
        $netSales = moneyFormatter($this->netSalesQuery($dateFrom, $dateTo)
            ->netSalesSum()
            ->value('net_sales_sum'));
        $ordersCount = moneyFormatter($this->ordersCountQuery($dateFrom, $dateTo, request('ids'))->count());
        $defaultCurrency = defaultCurrency();
        $charts = [];
        $getChartData = function ($dateFrom, $dateTo, $id = null) {
            switch (request('chart', 'commission_earned')) {
                case 'orders_count':
                    return $this->ordersCount($dateFrom, $dateTo, $id);
                case 'net_sales':
                    return $this->netSales($dateFrom, $dateTo, $id);
                case 'items_sold':
                default:
                    return $this->itemsSold($dateFrom, $dateTo, $id);
            }
        };

        foreach (request('ids') as $id) {
            $charts[] = [
                'translation' => $this->getCategoryById($id),
                'chart' => $getChartData($dateFrom, $dateTo, $id),
            ];
        }

        return compact('itemsSold', 'netSales', 'ordersCount', 'charts', 'defaultCurrency');
    }
}
