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

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $uuid
 * @property int $shop_id
 * @property int $category_id
 * @property int|null $unit_id
 * @property string|null $keywords
 * @property float|null $tax
 * @property int|null $min_qty
 * @property int|null $max_qty
 * @property int $active
 * @property string|null $img
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $bar_code
 * @property int|null $brand_id
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Discount> $discount
 * @property-read int|null $discount_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExtraGroup> $extras
 * @property-read int|null $extras_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderProduct> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderProduct> $productSales
 * @property-read int|null $product_sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductProperties> $properties
 * @property-read int|null $properties_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \App\Models\Shop $shop
 * @property-read Model|\Eloquent $stock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Stock> $stocks
 * @property-read int|null $stocks_count
 * @property-read \App\Models\ProductTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \App\Models\Unit|null $unit
 * @method static \Database\Factories\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Product filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product updatedDate($updatedDate)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereBarCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereMaxQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereMinQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereShopId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
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
