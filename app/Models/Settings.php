<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Settings extends Model
{
    use HasFactory;
    protected $guarded = [];
    const TTL = 2592000; // 30 days

    public static function adminSettings()
    {
        return Cache::remember('admin-settings', self::TTL, function (){
           return self::all();
        });
    }
}
