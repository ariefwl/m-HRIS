<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class attendance extends Model
{
    protected $table = 'attendances';
    protected $fillable = [
        'user_id',
        'nik',
        'check_type',
        'check_time',
        'latitude',
        'longitude',
        'accuracy',
        'distance_from_office',
        'device_info'
    ];
}
