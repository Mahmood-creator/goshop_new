<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory, SetCurrency, Loadable;
    protected $guarded = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, ProductDiscount::class);
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
                $q->where('type', $array['type']);
            })
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->where('active', $array['active']);
            });
    }}
