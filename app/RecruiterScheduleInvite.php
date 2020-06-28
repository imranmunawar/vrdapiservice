<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruiterScheduleInvite extends Model
{
    public function RecruiterDetails(){
        return $this->hasOne('App\User',  'id', 'recruiter_id');
    }
    public function RecruiterUserSetting(){
        return $this->hasOne('App\UserSettings',  'user_id', 'recruiter_id');
    }
    public function CandidateDetails(){
        return $this->hasOne('App\User',  'id', 'candidate_id');
    }
    public function FairDetails(){
        return $this->hasOne('App\Fair',  'id', 'fair_id');
    }

    public function RecruiterCompanyDetail(){
        return $this->hasOne('App\Company',  'id', 'company_id');
    }

    public function SlotInfo(){
        return $this->hasOne('App\RecruiterSchedule',  'id', 'slot_id');
    }


    protected $fillable = array(
        'u_id',
        'fair_id',
        'company_id',
        'recruiter_id',
        'candidate_id',
        'slot_id',
        'notes',
        'status'
     );
}
