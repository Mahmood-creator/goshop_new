<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StockExtra
 *
 * @property int $id
 * @property int $stock_id
 * @property int $extra_value_id
 * @method static \Database\Factories\StockExtraFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra whereExtraValueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockExtra whereStockId($value)
 * @mixin \Eloquent
 */
class StockExtra extends Model
{
    use HasFactory;
    public $timestamps = false;
}
