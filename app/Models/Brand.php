<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory, Loadable;
    protected $guarded = [];

    public function products(){
        return $this->hasMany(Product::class);
    }

    public function scopeUpdatedDate($query, $updatedDate)
    {
        return $query->where('updated_at', '>', $updatedDate);
    }

    /* Filter Scope */
    public function scopeFilter($value, $array)
    {
        return $value
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->whereActive($array['active']);
            });
    }

}
