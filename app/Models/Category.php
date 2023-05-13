<?php

namespace App\Models;

use App\Traits\Loadable;
use Database\Factories\CategoryFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $uuid
 * @property string|null $keywords
 * @property int $parent_id
 * @property int $type
 * @property string|null $img
 * @property int $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property float|null $weight
 * @property int|null $product_type_id
 * @property-read Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, Gallery> $galleries
 * @property-read int|null $galleries_count
 * @property-read Category|null $parent
 * @property-read Product|null $product
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read CategoryTranslation|null $translation
 * @property-read Collection<int, CategoryTranslation> $translations
 * @property-read int|null $translations_count
 * @method static CategoryFactory factory(...$parameters)
 * @method static Builder|Category filter($array)
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category onlyTrashed()
 * @method static Builder|Category query()
 * @method static Builder|Category updatedDate($updatedDate)
 * @method static Builder|Category whereActive($value)
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereDeletedAt($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereImg($value)
 * @method static Builder|Category whereKeywords($value)
 * @method static Builder|Category whereParentId($value)
 * @method static Builder|Category whereProductTypeId($value)
 * @method static Builder|Category whereType($value)
 * @method static Builder|Category whereUpdatedAt($value)
 * @method static Builder|Category whereUuid($value)
 * @method static Builder|Category whereWeight($value)
 * @method static Builder|Category withTrashed()
 * @method static Builder|Category withoutTrashed()
 * @mixin Eloquent
 */
class Category extends Model
{
    use HasFactory, Loadable, SoftDeletes;

    protected $guarded = [];

    const TYPES = [
        'main' => 1,
        'blog' => 2,
        'brand' => 3
    ];

    public function getTypeAttribute($value)
    {
        foreach (self::TYPES as $index => $type) {
            if ($type === $value) {
                return $index;
            }
        }
    }

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(CategoryTranslation::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->with('children.translation');
    }

    public function grandchildren()
    {
        return $this->children()->with('grandchildren');
    }

//    public function grandchildren()
//    {
//        return $this->children()->with('grandchildren');
//
//    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class);
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    /* Filter Scope */
    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', '=', Category::TYPES[$array['type']]);
            })
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->whereActive($array['active']);
            })
            ->when(isset($array['length']), function ($q) use ($array) {
                $q->skip($array['start'] ?? 0)->take($array['length']);
            });
    }
}
