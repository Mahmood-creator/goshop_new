<?php

namespace App\Models;

use App\Traits\Payable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model
{
    use HasFactory,Payable,Reviewable;

    protected $guarded = [];


    const NEW = 1;
    const READY = 2;
    const DECLARATION_IN_ADVANCE = 5;
    const EXTERNAL_WAREHOUSE = 7;
    const ON_THE_WAY = 8;
    const AT_CUSTOMS = 36;
    const INTERNAL_WAREHOUSE = 9;
    const HANDED_OVER = 10;
    const COURIER = 11;
    const DELIVERED = 12;
    const CANCELED = 13;

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
