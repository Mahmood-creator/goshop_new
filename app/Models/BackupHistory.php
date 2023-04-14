<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupHistory extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function getDates()
    {
        return ['created_at'];
    }

    public function user() {
        return $this->belongsTo(User::class, 'created_by');
    }

}
