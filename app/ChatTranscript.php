<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatTranscript extends Model
{
	public function userFrom()
  	{
	    return $this->hasOne('App\User', 'id', 'from');
  	}
	public function userTo()
  	{
	    return $this->hasOne('App\User', 'id', 'to');
  	}

    protected $fillable = ['id', 'from', 'to', 'message', 'sent', 'fair_id', 'company_id'];

    public $timestamps = false;
}
