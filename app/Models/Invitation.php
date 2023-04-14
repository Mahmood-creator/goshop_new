<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;
    protected $guarded = [];

    const STATUS = [
        'new' => 1,
        'viewed' => 2,
        'excepted' => 3,
        'rejected' => 4
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getStatusKey($value)
    {
        foreach (self::STATUS as $index => $status) {
            if ($value == $status){
                return $index;
            }
        }
    }

    public function scopeFilter($query, $array)
    {
        $query->when(isset($array['user_id']), function ($q) use($array) {
            $q->where('user_id', $array['user_id']);
        })->when(isset($array['shop_id']), function ($q) use($array) {
            $q->where('shop_id', $array['shop_id']);
        });
    }
}
