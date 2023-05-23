<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\City
 *
 * @property int $id
 * @property int $region_id
 * @property string|null $name
 * @property boolean|true $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|City filter($filter)
 * @method static Builder|City newModelQuery()
 * @method static Builder|City newQuery()
 * @method static Builder|City query()
 * @method static Builder|City whereCreatedAt($value)
 * @method static Builder|City whereId($value)
 * @method static Builder|City whereName($value)
 * @method static Builder|City whereRegionId($value)
 * @method static Builder|City whereUpdatedAt($value)
 * @mixin Eloquent
 */
class City extends Model
{
    use HasFactory;
    protected $fillable = ['name','status','region_id'];

    public function scopeFilter($query, $filter)
    {
        return $query->when(isset($filter['search']), function ($q) use ($filter) {
            $q->where('name', 'LIKE', '%'. $filter['search'] . '%');
        })->when(isset($filter['region_id']),function ($q) use ($filter){
            $q->where('region_id',$filter['region_id']);
        })->when(isset($filter['status']),function ($q) use ($filter){
            $q->where('status',$filter['status']);
        });
    }
}
