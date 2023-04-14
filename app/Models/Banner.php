<?php

namespace App\Models;

use App\Traits\Likable;
use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory, Loadable, Likable;
    protected $guarded = [];

    protected $casts = [
        'products' => 'array'
    ];

    const TYPES = [
        'banner',
        'look',
    ];

    // Translations
    public function translations() {
        return $this->hasMany(BannerTranslation::class);
    }

    public function translation() {
        return $this->hasOne(BannerTranslation::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class)->withDefault();
    }
}
