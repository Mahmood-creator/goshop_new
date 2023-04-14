<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    protected $fillable = ['uuid', 'type', 'active'];

    public function translations() {
        return $this->hasMany(FaqTranslation::class);
    }

    public function translation() {
        return $this->hasOne(FaqTranslation::class);
    }
}
