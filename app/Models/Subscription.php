<?php

namespace App\Models;

use App\Traits\Payable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['active', 'price', 'month', 'type'];
    const TTL = 2592000; // 30 days

    public static function subscriptionsList()
    {
        return Cache::remember('subscriptions-list', self::TTL, function (){
            return self::all();
        });
    }
}
