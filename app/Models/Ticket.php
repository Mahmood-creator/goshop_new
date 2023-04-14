<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
