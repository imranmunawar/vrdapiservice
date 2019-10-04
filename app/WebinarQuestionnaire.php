<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebinarQuestionnaire extends Model
{
    protected $fillable = [
        'fair_id','webinar_id','test_id','answer', 'score'
    ];
}
