<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CometChatPro extends Model
{
    
    protected $fillable = [
        'organizer_id',
        'app_id',
        'rest_api_key',
        'api_key',
        'region'
    ];
}
