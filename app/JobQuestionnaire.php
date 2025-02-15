<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobQuestionnaire extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_id','test_id','answer', 'score'
    ];
}
