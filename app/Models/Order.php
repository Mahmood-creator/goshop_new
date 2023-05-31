<?php

namespace App\Models;

use App\Traits\Payable;
use App\Traits\Reviewable;
use Database\Factories\OrderFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property float $price
 * @property int $currency_id
 * @property int $rate
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property string|null $name
 * @property string|null $phone
 * @property float|null $usd_price
 * @property string|null $status
 * @property float|null $total_delivery_fee
 * @property int|null $user_address_id
 * @property int|null $track_code
 * @property int|null $declaration_id
 * @property float|null $tax
 * @property int $delivery_id
 * @property string $delivery_type
 * @property int|null $country_id
 * @property int|null $product_type_id
 * @property int|null $deliveryman_id
 * @property-read OrderCoupon|null $coupon
 * @property-read Currency|null $currency
 * @property-read Delivery $delivery
 * @property-read User|null $deliveryMan
 * @property-read OrderDetail|null $orderDetail
 * @property-read Collection<int, OrderDetail> $orderDetails
 * @property-read int|null $order_details_count
 * @property-read PointHistory|null $point
 * @property-read Review|null $review
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Transaction|null $transaction
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read User $user
 * @property-read UserAddress|null $userAddress
 * @method static OrderFactory factory(...$parameters)
 * @method static Builder|Order filter($array)
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order updatedDate($updatedDate)
 * @method static Builder|Order whereCountryId($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCurrencyId($value)
 * @method static Builder|Order whereDeclarationId($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereDeliveryId($value)
 * @method static Builder|Order whereDeliverymanId($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereProductTypeId($value)
 * @method static Builder|Order whereRate($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereTax($value)
 * @method static Builder|Order whereTotalDeliveryFee($value)
 * @method static Builder|Order whereTrackCode($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUsdPrice($value)
 * @method static Builder|Order whereUserAddressId($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    use HasFactory,Payable,Reviewable;

    protected $guarded = [];


    const NEW = 'new';
    const READY = 'ready';
    const ACCEPTED = 'accepted';
    const ON_A_WAY = 'on_a_way';
    const DELIVERED = 'delivered';
    const COMPLETED = 'completed';
    const CANCELED = 'canceled';

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderDetail(): HasOne
    {
        return $this->hasOne(OrderDetail::class);
    }

//    public function transaction(){
//        return $this->hasOneThrough(Transaction::class, OrderDetail::class,
//        'order_id', 'payable_id', 'id', 'id');
//    }

    public function transaction(): MorphOne
    {
        return $this->morphOne( Transaction::class,'payable')->orderByDesc('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review() {
        return $this->morphOne(Review::class, 'reviewable');
    }

    public function point() {
        return $this->hasOne(PointHistory::class, 'order_id');
    }

    public function userAddress()
    {
        return $this->belongsTo(UserAddress::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(OrderCoupon::class, 'order_id');
    }

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliveryman_id');
    }

    public function getPriceAttribute($value)
    {
        $rate = Currency::where('id',$this->currency_id)->first()->rate;
        if (request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }

    public function getTotalDeliveryFeeAttribute($value)
    {
        $rate = Currency::where('id',$this->currency_id)->first()->rate;
        if (request()->is('api/v1/dashboard/user/*')){
            return round($value * $rate, 2);
        } else {
            return $value;
        }
    }


    public function getStatusAttribute()
    {
        if ($this->orderDetails()
            ->whereIn('status', ['new', 'accepted', 'ready', 'on_a_way', 'paid'])
            ->exists()) {
            return 'open';
        } elseif ($this->orderDetails()
            ->whereIn('status', ['delivered'])
            ->exists()) {
            return 'delivered';
        } elseif ($this->orderDetails()
            ->whereIn('status', ['canceled'])
            ->exists()) {
            return 'canceled';
        }
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['status']) && $array['status'] == 1, function ($q) use ($array) {
                $q->whereIn('status', [1,2,5,7,8,36,9,10,11]);
            })
            ->when(isset($array['status']) && $array['status'] == 12, function ($q) use ($array) {
                $q->where('status', 12);
            })
            ->when(isset($array['status']) && $array['status'] == 13, function ($q) use ($array) {
                $q->where('status', 13);
            });
    }
}
