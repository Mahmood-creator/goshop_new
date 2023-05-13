<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ProductExtra
 *
 * @property int $id
 * @property int $product_id
 * @property int $extra_group_id
 * @property-read \App\Models\ExtraGroup $extras
 * @method static \Database\Factories\ProductExtraFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra whereExtraGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductExtra whereProductId($value)
 * @mixin \Eloquent
 */
class ProductExtra extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['extra_value_id', 'price'];

    public function extras(){
        return $this->belongsTo(ExtraGroup::class, 'extra_group_id');
    }
}
