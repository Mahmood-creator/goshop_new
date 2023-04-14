<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;
    public $timestamps = false;

    const TYPES = [
        'banners', 'brands',
        'categories', 'languages',
        'shops', 'shops/logo', 'shops/background',
        'users', 'products', 'extras', 'reviews',
        'blogs', 'coupons', 'discounts'
    ];

    protected $fillable = ['title','loadable_type','loadable_id','type','path'];

    public function loadable() {
        return $this->morphTo('loadable');
    }
}
