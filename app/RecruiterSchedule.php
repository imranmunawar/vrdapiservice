<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecruiterSchedule extends Model
{
    
  public function RecruiterDetails()
  {
    return $this->hasOne('App\User',  'id', 'recruiter_id');
  }

  protected $fillable = array(
      'fair_id',
      'company_id',
      'recruiter_id',
      'candidate_id',
      'start_time',
      'end_time',
      'days',
      'days_arr',
      'available'
  );

}
