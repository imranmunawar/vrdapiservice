<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareerTestAnswer extends Model
{
    protected $fillable = [
       'test_id',
       'answer',
       'score'
    ];
}
