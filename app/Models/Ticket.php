<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property string $uuid
 * @property int $created_by
 * @property int|null $user_id
 * @property int|null $order_id
 * @property int $parent_id
 * @property string $type
 * @property string $subject
 * @property string $content
 * @property string $status
 * @property int $read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Ticket> $children
 * @property-read int|null $children_count
 * @method static \Database\Factories\TicketFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket filter($array)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUuid($value)
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    use HasFactory;
    protected $guarded = [];

    const STATUS = [
        'open',
        'answered',
        'progress',
        'closed',
        'rejected',
    ];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeFilter($query, $array){
        $query
            ->when(isset($array['status']), function ($q) use ($array) {
            $q->where('status', $array['status']);
            })
            ->when(isset($array['created_by']), function ($q) use ($array) {
                $q->where('created_by', $array['created_by']);
            })
            ->when(isset($array['user_id']), function ($q) use ($array) {
                $q->where('user_id', $array['user_id']);
            })
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', $array['type']);
            });
    }
}
