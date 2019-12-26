<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    protected $fillable = [
        'fair_id',
        'notification_type'
    ];
}
