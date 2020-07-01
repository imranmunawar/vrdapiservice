<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterviewCompleteNote extends Model
{
    protected $fillable = array(
        'slot_id',
        'candidate_id',
        'recruiter_id',
        'notes'
    );
}
