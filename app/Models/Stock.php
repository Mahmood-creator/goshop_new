<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Stock
 *
 * @property int $id
 * @property string $countable_type
 * @property int $countable_id
 * @property float $price
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $url
 * @property-read Model|\Eloquent $countable
 * @property-read \App\Models\Discount|null $discount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StockExtra> $extras
 * @property-read int|null $extras_count
 * @property-read mixed $actual_discount
 * @property-read mixed $discount_expired
 * @property-read mixed $tax_price
 * @property-write mixed $currency_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExtraValue> $stockExtras
 * @property-read int|null $stock_extras_count
 * @method static \Database\Factories\StockFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock query()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereCountableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereCountableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Stock withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Stock withoutTrashed()
 * @mixin \Eloquent
 */
class Stock extends Model
{
    use HasFactory, SoftDeletes,SetCurrency;
    protected $fillable = ['price', 'quantity', 'extras','countable_id','countable_type','url'];
    public $timestamps = false;

    protected $casts = [
        'extras' => 'array'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function countable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('countable')->withTrashed();
    }

    public function discount(){
        return $this->hasOneThrough(Discount::class, ProductDiscount::class,
            'product_id', 'id', 'countable_id', 'discount_id')
            ->whereDate('start', '<=', today())->whereDate('end', '>=', today())
            ->where('active', 1)->orderByDesc('id');
    }

    public function stockExtras()
    {
        return $this->belongsToMany(ExtraValue::class, StockExtra::class)->orderBy('extra_group_id');
    }

    public function extras()
    {
        return $this->hasMany(StockExtra::class);
    }

    public function getPriceAttribute($value)
    {
        return $value * $this->currency();
    }

    public function setCurrencyIdAttribute()
    {
        $this->attributes['currency_id'] = request('currency_id');
    }

    public function getActualDiscountAttribute($value)
    {

        if (isset($this->discount->type)) {
            if ($this->discount->type == 'percent') {
                $price = $this->discount->price / 100 * $this->price;
            } else {
                $price = $this->discount->price * $this->currency();
            }
            return $price;
        }
        return 0;
    }

    public function getDiscountExpiredAttribute($value)
    {

        return $this->discount->end ?? null;
    }

    public function getTaxPriceAttribute($value)
    {
        $tax = $this->countable->tax ?? 0;
        return (($this->price - $this->actualDiscount) / 100) * $tax;
    }
}
