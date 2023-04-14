<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'symbol', 'rate', 'active','position','default','active','short_code','currency_code'];
    const TTL = 86400; // 1 day

    public static function currenciesList()
    {
        return Cache::remember('currencies-list', self::TTL, function (){
            return self::orderByDesc('default')->get();
        });
    }
}
