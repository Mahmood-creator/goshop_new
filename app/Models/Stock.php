<?php

namespace App\Models;

use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

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
