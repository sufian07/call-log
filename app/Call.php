<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'call_date',
        'phone_number',
        'call_duration',
        'status',
    ];
}
