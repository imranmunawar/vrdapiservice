<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingChannel extends Model
{
	
    protected $fillable = [
       'fair_id',
       'channel_name',
       'cost',
       'url',
       'clicks',
       'notes'
     ];
}
