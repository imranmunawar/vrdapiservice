<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyStand extends Model
{
     protected $fillable = array(
        'company_id',
        'stand_top',
        'stand_left',
        'stand_width',
        'stand_height'
    );
}
