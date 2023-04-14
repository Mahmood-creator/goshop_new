<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function scopeFilter($query, $array = [])
    {
        return $query->when(isset($array['group']), function ($q)  use ($array) {
            $q->where('group', $array['group']);
        })->when(isset($array['locale']), function ($q)  use ($array) {
            $q->where('locale', $array['locale']);
        });
    }
}
