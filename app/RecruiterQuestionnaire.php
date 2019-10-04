<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruiterQuestionnaire extends Model
{
    protected $fillable = [
        'fair_id','recruiter_id','test_id','answer', 'score'
    ];
}
