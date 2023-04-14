<?php

namespace App\Models;

use App\Traits\Countable;
use App\Traits\Loadable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Product extends Model
{
    use HasFactory, SoftDeletes, Countable, Loadable, Reviewable, SetCurrency;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation() {
        return $this->hasOne(ProductTranslation::class);
    }

    // Product Shop
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    // Product Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Product Orders
    public function productSales(): HasMany
    {
        return $this->hasMany(OrderProduct::class, 'product_id');
    }

    // Product Brand
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    // Product Properties
    public function properties(): HasMany
    {
        return $this->hasMany(ProductProperties::class);
    }

    public function orders()
    {
        return $this->hasManyThrough(OrderProduct::class, Stock::class,
            'countable_id', 'stock_id', 'id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function extras()
    {
        return $this->belongsToMany(ExtraGroup::class, ProductExtra::class);
    }

    public function discount()
    {
        return $this->belongsToMany(Discount::class, ProductDiscount::class);
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    public function scopeFilter($query, $array)
    {
        $query
            ->when(isset($array['range'][0]) || isset($array['range'][1]), function ($q) use ($array) {
                $q->whereHas('stocks', function ($stock) use($array){
                    $stock->whereBetween('price', [$array['range'][0] ?? 0.1, $array['range'][1] ?? 10000000000]);
                });
            })
            ->when(isset($array['shop_id']), function ($q) use ($array) {
                $q->where('shop_id', $array['shop_id']);
            })
            ->when(isset($array['category_id']) && is_array($array['category_id']) , function ($q) use ($array) {
                $q->whereIn('category_id', $array['category_id']);
            })
            ->when(isset($array['category_id']) && is_string($array['category_id']) , function ($q) use ($array) {
                $q->where('category_id', $array['category_id']);
            })
            ->when(isset($array['brand_id']), function ($q) use ($array) {
                $q->where('brand_id', $array['brand_id']);
            })
            ->when(isset($array['column_rate']), function ($q) use ($array) {
                $q->whereHas('reviews', function ($review) use($array){
                    $review->orderBy('rating', $array['sort']);
                });
            })
            ->when(isset($array['column_order']), function ($q) use ($array) {
                $q->withCount('orders')->orderBy('orders_count', $array['sort']);
            })
            ->when(isset($array['column_price']), function ($q) use ($array) {
                $q->withAvg('stocks', 'price')->orderBy('stocks_avg_price', $array['sort']);
            });
    }
}
