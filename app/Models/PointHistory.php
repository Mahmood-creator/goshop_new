<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PointHistory
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property float $price
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PointHistory whereUserId($value)
 * @mixin \Eloquent
 */
class PointHistory extends Model
{
    use HasFactory;
    protected $guarded = [];

}
