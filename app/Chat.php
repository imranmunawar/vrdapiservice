<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
     protected $fillable = [
        'user_chat_id','chat_status'
    ];
}
