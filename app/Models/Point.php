<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function getActualPoint(string $amount)
    {
        $point = self::where('active', 1)->where('value', '<=', (int) $amount)->orderByDesc('value')->first();

        if (isset($point) && $point->type == 'percent') {
            $price = ($amount / 100) * $point->price;
        } elseif(isset($point) && $point->type == 'fix') {
            $price = $point->price;
        } else {
            $price = 0;
        }
        
        return $price;
    }
}
