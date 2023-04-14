<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraValue extends Model
{
    use HasFactory, Loadable;
    protected $fillable = ['value', 'active','extra_group_id'];
    public $timestamps = false;

    public function group()
    {
        return $this->belongsTo(ExtraGroup::class, 'extra_group_id');
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, StockExtra::class);
    }

}
