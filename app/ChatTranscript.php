<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatTranscript extends Model
{
	public function userSender()
  	{
	    return $this->hasOne('App\User', 'id', 'sender_id');
  	}
	public function userReceiver()
  	{
	    return $this->hasOne('App\User', 'id', 'receiver_id');
  	}

    protected $fillable = [
      'id',
      'sender_id',
      'receiver_id',
      'category',
      'type',
      'sender_role',
      'receiver_role',
      'sender_name',
      'receiver_name',
      'sender_avatar',
      'receiver_avatar',
      'message',
      'extension',
      'sent_at',
      'fair_id',
      'company_id'
    ];

    public $timestamps = false;
}
