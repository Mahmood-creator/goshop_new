<?php

namespace App\Models;

use App\Traits\Payable;
use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * App\Models\ShopSubscription
 *
 * @property int $id
 * @property int $shop_id
 * @property int $subscription_id
 * @property string|null $expired_at
 * @property float|null $price
 * @property string|null $type
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $currency_id
 * @property-read \App\Models\Currency|null $currency
 * @property-read \App\Models\Shop $shop
 * @property-read \App\Models\Subscription|null $subscription
 * @property-read \App\Models\Transaction|null $transaction
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription actualSubscription()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereSubscriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShopSubscription whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShopSubscription extends Model
{
    use HasFactory, Payable, SetCurrency;
    protected $guarded = [];


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne( Transaction::class,'payable');
    }

    public function scopeActualSubscription($query)
    {
        return $query->where('created_at', '>=', now())
            ->where('expired_at', '<=', now());
    }
}
