<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingChannel extends Model
{
	
    protected $fillable = [
       'fair_id',
       'channel_name',
       'channel_logo',
       'cost',
       'url',
       'clicks',
       'notes'
     ];
}
