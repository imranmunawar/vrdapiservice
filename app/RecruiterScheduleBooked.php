<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruiterScheduleBooked extends Model
{
  public function RecruiterDetails()
  {
    return $this->hasOne('App\User',  'id', 'recruiter_id');
  }
  public function CandidateDetails()
  {
    return $this->hasOne('App\User',  'id', 'candidate_id');
  }

  public function userSetting()
  {
    return $this->hasOne('App\UserSettings',  'user_id', 'candidate_id');
  }

  public function FairDetails()
  {
    return $this->hasOne('App\Fair',  'id', 'fair_id');
  }
  protected $fillable = array(
      'u_id',
      'fair_id',
      'candidate_id',
      'recruiter_id',
      'start_time',
      'end_time',
      'date',
      'attended',
      'is_approved',
      'meeting_id',
      'host_id',
      'start_url',
      'join_url',
      'password'
  );
}
