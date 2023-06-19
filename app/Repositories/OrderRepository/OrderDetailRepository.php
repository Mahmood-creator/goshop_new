<?php

namespace App\Repositories\OrderRepository;

use App\Models\OrderDetail;
use App\Models\Stock;
use App\Models\User;
use App\Repositories\CoreRepository;
use Illuminate\Support\Facades\DB;

class OrderDetailRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return OrderDetail::class;
    }

    public function paginate(int $perPage = 15, int $userId = null, array $array = [])
    {
        return $this->model()
            ->with([
                'order.currency' => function ($q) {
                    $q->select('id', 'title', 'symbol');
                },
                'order.transaction.payment.translation',
                'shop',
                'order.user'
            ])
            ->filter($array)
            ->when(isset($userId), function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->paginate($perPage);
    }

    public function getById(int $id)
    {
        return $this->model()->with([
            'order',
            'order.user',
            'deliveryType',
            'order.userAddress',
            'deliveryMan',
            'order.coupon',
            'shop.translation',
            'order.transaction.payment.translation:id,locale,payment_id,title',
            'orderStocks.stock.countable.translation:id,product_id,locale,title',
            'order.currency:id,title,symbol'
        ])->find($id);
    }

    public function orderProductsCalculate($array)
    {
        // Get Product ID from Request
        $id = collect($array['products'])->pluck('id');

        // Find Products in DB
        $products = Stock::with('countable.shop')->find($id);
        $products = $products->map(function ($item) use ($array) {
            $quantity = $item->quantity;  // Set Stock Quantity
            $price = $item->price;  // Set Stock price
            foreach ($array['products'] as $product) {
                if ($item->id == $product['id']) {
                    // Set new Product quantity if it less in the stock
                    $quantity = min($item->quantity, $product['quantity']);
                }
            }

            // Get Product Price Tax minus discount
            $tax = (($price - $item->actualDiscount) / 100) * ($item->countable->tax ?? 0);
            // Get Product Price without Tax for Order Total
            $priceWithoutTax = ($price - $item->actualDiscount) * $quantity;
            // Get Product Shop Tax amount
            $shopTax = ($priceWithoutTax / 100 * ($item->countable->shop->tax ?? 0));
            // Get Total Product Price with Tax, Discount and Quantity
            $totalPrice = (($price - $item->actualDiscount) + $tax) * $quantity;

            return [
                'id' => (int)$item->id,
                'price' => round($price, 2),
                'qty' => (int)$quantity,
                'tax' => round(($tax * $quantity), 2),
                'shop_tax' => round($shopTax, 2),
                'discount' => round(($item->actualDiscount * $quantity), 2),
                'price_without_tax' => round($priceWithoutTax, 2),
                'total_price' => round($totalPrice, 2),
            ];
        });

        return [
            'products' => $products,
            'product_tax' => $products->sum('tax'),
            'product_total' => round($products->sum('price_without_tax'), 2),
            'order_tax' => round($products->sum('shop_tax'), 2),
            'order_total' => round($products->sum('price_without_tax') + $products->sum('tax') + $products->sum('shop_tax'), 2)
        ];
    }


    public function deliveryManReport(array $filter = []): array
    {
        $type     = data_get($filter, 'type', 'day');
        $dateFrom = date('Y-m-d 00:00:01', strtotime(request('date_from')));
        $dateTo   = date('Y-m-d 23:59:59', strtotime(request('date_to', now())));
        $now      = now()?->format('Y-m-d 00:00:01');
        $user     = User::withAvg('reviews', 'rating')
            ->with(['wallet'])
            ->find(data_get($filter, 'deliveryman'));

        $lastOrder = DB::table('order_details')
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $orders = DB::table('order_details')
            ->where('deliveryman', data_get($filter, 'deliveryman'))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->select([
                DB::raw("sum(if(status = 'delivered', delivery_fee, 0)) as delivery_fee"),
                DB::raw('count(id) as total_count'),
                DB::raw("sum(if(created_at >= '$now', 1, 0)) as total_today_count"),
                DB::raw("sum(if(status = 'new', 1, 0)) as total_new_count"),
                DB::raw("sum(if(status = 'ready', 1, 0)) as total_ready_count"),
                DB::raw("sum(if(status = 'on_a_way', 1, 0)) as total_on_a_way_count"),
                DB::raw("sum(if(status = 'accepted', 1, 0)) as total_accepted_count"),
                DB::raw("sum(if(status = 'canceled', 1, 0)) as total_canceled_count"),
                DB::raw("sum(if(status = 'delivered', 1, 0)) as total_delivered_count"),
            ])
            ->first();

        $type = match ($type) {
            'year' => '%Y',
            'week' => '%w',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $chart = DB::table('order_details')
            ->where('deliveryman', data_get($filter, 'deliveryman'))
            ->where('created_at', '>=', $dateFrom)
            ->where('created_at', '<=', $dateTo)
            ->whereNull('deleted_at')
            ->where('status', OrderDetail::DELIVERED)
            ->select([
                DB::raw("(DATE_FORMAT(created_at, '$type')) as time"),
                DB::raw('sum(delivery_fee) as total_price'),
            ])
            ->groupBy('time')
            ->orderBy('time')
            ->get();

        return [
            'last_order_total_price' => (int)ceil(data_get($lastOrder, 'total_price', 0)),
            'last_order_income' => (int)ceil(data_get($lastOrder, 'delivery_fee', 0)),
            'total_price' => (int)data_get($orders, 'delivery_fee', 0),
            'avg_rating' => $user->assign_reviews_avg_rating,
            'wallet_price' => $user->wallet?->price,
            'wallet_currency' => $user->wallet?->currency,
            'total_count' => (int)data_get($orders, 'total_count', 0),
            'total_today_count' => (int)data_get($orders, 'total_today_count', 0),
            'total_new_count' => (int)data_get($orders, 'total_new_count', 0),
            'total_ready_count' => (int)data_get($orders, 'total_ready_count', 0),
            'total_on_a_way_count' => (int)data_get($orders, 'total_on_a_way_count', 0),
            'total_accepted_count' => (int)data_get($orders, 'total_accepted_count', 0),
            'total_canceled_count' => (int)data_get($orders, 'total_canceled_count', 0),
            'total_delivered_count' => (int)data_get($orders, 'total_delivered_count', 0),
            'chart' => $chart
        ];
    }

}
