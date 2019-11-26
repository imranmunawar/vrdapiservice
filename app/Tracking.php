<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    public function user(){
        return $this->hasOne('App\User',  'id', 'user_id')->select('id','name','email');
    }
    public function fair(){
        return $this->hasOne('App\Fair',  'id', 'fair_id')->select('id','name');
    }
    protected $fillable = array(
        'user_id',
        'ip',
        'location',
        'device',
        'browser',
        'fair_id',
        'referrer',
        'u_id',
        'expiry'
    );
}
