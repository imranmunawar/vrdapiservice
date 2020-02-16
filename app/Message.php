<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public function userFromSetting()
    {
       return $this->hasOne('App\UserSettings', 'user_from_id');
    }

    public function userToSetting()
    {
       return $this->hasOne('App\UserSettings', 'user_to_id');
    }


    protected $fillable = [
        'chat_id','from_user_id', 'to_user_id','message_from_role','message','created_at'
    ];
}
