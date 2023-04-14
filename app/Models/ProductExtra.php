<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductExtra extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['extra_value_id', 'price'];

    public function extras(){
        return $this->belongsTo(ExtraGroup::class, 'extra_group_id');
    }
}
