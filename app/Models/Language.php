<?php

namespace App\Models;

use App\Traits\Loadable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory, Loadable;
    protected $guarded = [];
    public $timestamps = false;

    public static function languagesList(){
        return cache()->remember('languages-list', 84300, function (){
           return self::orderByDesc('id')->get();
        });
    }
}
