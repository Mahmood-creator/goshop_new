<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

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
