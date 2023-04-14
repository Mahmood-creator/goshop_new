<?php

namespace App\Models;

use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes, Payable;
    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function histories() {
        return $this->hasMany(WalletHistory::class, 'wallet_uuid', 'uuid');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getPriceAttribute($value)
    {
        $currency = request()->currency_id
            ? Currency::currenciesList()->where('id', request()->currency_id)->first()
            : Currency::currenciesList()->where('default', 1)->first();

        return round($value * $currency->rate, 2);
    }

    public function getSymbolAttribute($value)
    {
        $currency = request()->currency_id
            ? Currency::currenciesList()->where('id', request()->currency_id)->first()
            : Currency::currenciesList()->where('default', 1)->first();
        return  $currency->symbol;
    }
}
