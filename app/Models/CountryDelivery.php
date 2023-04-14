<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountryDelivery extends Model
{
    use HasFactory;

    protected $fillable = ['country_id','delivery_id','price'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }
}
