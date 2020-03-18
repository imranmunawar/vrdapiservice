<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateScheduleNote extends Model
{
    protected $fillable = array(
        'slot_id',
        'candidate_id',
        'notes'
     );
}
