<?php

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory, Loadable, Reviewable;

    protected $fillable = ['uuid', 'user_id', 'type', 'published_at', 'active', 'img'];

    const TYPES = [
        'blog' => 1,
        'notification' => 2,
    ];

    public function getTypeAttribute($value)
    {
        foreach (self::TYPES as $index => $type) {
            if ($type === $value){
                return $index;
            }
        }
    }

    public function translations() {
        return $this->hasMany(BlogTranslation::class);
    }

    public function translation() {
        return $this->hasOne(BlogTranslation::class);
    }
}
