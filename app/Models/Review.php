<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, Loadable;
    protected $fillable = ['user_id', 'rating', 'comment', 'img'];


    public function reviewable()
    {
        return $this->morphTo('reviewable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
