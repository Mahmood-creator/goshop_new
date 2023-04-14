<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProperties extends Model
{
    use HasFactory;
    protected $fillable = ['locale', 'key', 'value'];
    public $timestamps = false;

    public function scopeActualTranslation($query, $lang)
    {
        $lang = self::where('locale', $lang)->pluck('locale')->first() ?? self::pluck('locale')->first();
        return $query->where('locale', $lang);
    }
}
