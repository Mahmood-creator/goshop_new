<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;
    protected $fillable = ['shop_id', 'price', 'times', 'note', 'active', 'type'];
    protected $casts = [
      'times' => 'array'
    ];

    const TYPES = [
        'pickup',
        'free',
        'standard',
        'express',
    ];

    public function translations() {
        return $this->hasMany(DeliveryTranslation::class);
    }

    public function translation() {
        return $this->hasOne(DeliveryTranslation::class);
    }

    public function getPriceAttribute($value)
    {
        $currency = isset(request()->currency_id)
            ? Currency::currenciesList()->where('id', request()->currency_id)->first()
            : Currency::currenciesList()->where('default', 1)->first();

        return round($value * $currency->rate, 2);
    }

    public function countries()
    {
        return $this->belongsToMany(Country::class,CountryDelivery::class)->withPivot('price');
    }

    public function scopeFilter()
    {

    }
}
