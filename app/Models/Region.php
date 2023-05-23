<?php

namespace App\Models;

use Eloquent;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Region
 *
 * @property int $id
 * @property int $country_id
 * @property string|null $name
 * @property boolean|true $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Region filter($filter)
 * @method static Builder|Region newModelQuery()
 * @method static Builder|Region newQuery()
 * @method static Builder|Region query()
 * @method static Builder|Region whereCountryId($value)
 * @method static Builder|Region whereCreatedAt($value)
 * @method static Builder|Region whereId($value)
 * @method static Builder|Region whereName($value)
 * @method static Builder|Region whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Region extends Model
{
    use HasFactory;
    protected $fillable = ['name','status','country_id'];

    public function scopeFilter($query, $filter)
    {
        return $query->when(isset($filter['search']), function ($q) use ($filter) {
            $q->where('name', 'LIKE', '%' . $filter['search'] . '%');
        })->when(isset($filter['country_id']), function ($q) use ($filter) {
            $q->where('country_id', $filter['country_id']);
        })->when(isset($filter['status']),function ($q) use ($filter){
            $q->where('status',$filter['status']);
        });
    }
}
