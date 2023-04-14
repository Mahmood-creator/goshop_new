<?php

namespace App\Models;

use App\Traits\Payable;
use App\Traits\SetCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ShopSubscription extends Model
{
    use HasFactory, Payable, SetCurrency;
    protected $guarded = [];


    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne( Transaction::class,'payable');
    }

    public function scopeActualSubscription($query)
    {
        return $query->where('created_at', '>=', now())
            ->where('expired_at', '<=', now());
    }
}
