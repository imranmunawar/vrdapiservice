<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruiterScheduleInvite extends Model
{
    public function RecruiterDetails()
    {
        return $this->hasOne('App\User',  'id', 'recruiter_id');
    }
     public function CandidateDetails()
     {
        return $this->hasOne('App\User',  'id', 'candidate_id');
     }
     public function FairDetails()
     {
        return $this->hasOne('App\Fair',  'id', 'fair_id');
     }

    public function SlotInfo()
    {
        return $this->hasOne('App\RecruiterSchedule',  'id', 'slot_id');
    }


    protected $fillable = array(
        'u_id',
        'fair_id',
        'recruiter_id',
        'candidate_id',
        'slot_id',
        'notes',
        'cancel'
     );
}
