<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareerTest extends Model
{
     protected $fillable = [
        'admin_id',
        'fair_id',
        'question',
        'short_question',
        'backoffice_question',
        'question_type',
        'min_selection',
        'max_selection',
        'display_order'
    ];

    public function answers()
    {
        return $this->hasMany('App\CareerTestAnswer', 'test_id','id');
    }
}
