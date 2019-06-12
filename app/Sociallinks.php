<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sociallinks extends Model
{
    public function fairs()
    {
       return $this->belongsTo(Fair::class);
    }
}
