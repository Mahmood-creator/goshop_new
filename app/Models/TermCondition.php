<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermCondition extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Translations
    public function translations() {
        return $this->hasMany(TermConditionTranslation::class);
    }

    public function translation() {
        return $this->hasOne(TermConditionTranslation::class);
    }

}
