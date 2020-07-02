<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
class CandidateJob extends Model {

    // public function applydetails()
    // {
    //     return $this->belongsTo('App\User', 'candidate_id', 'id' );
    // }
    public function jobs()
    {
        return $this->belongsTo('App\CompanyJob', 'job_id', 'id' );
    }
    // public function jobdetails()
    // {
    //     return $this->hasOne('App\MatchJobs', 'job_id', 'job_id' );
    // }

    public function candidate(){
      return $this->hasOne('App\User', 'id', 'candidate_id' );
    }

    public function match(){
      return $this->hasOne('App\MatchJob', 'job_id', 'job_id' );
    }

    public function candidateInfo(){
      return $this->hasOne('App\UserSettings', 'user_id', 'candidate_id' );
    }

    public function job(){
      return $this->hasOne('App\CompanyJob', 'id', 'job_id' );
    }

    protected $fillable = array(
      'candidate_id',
      'job_id',
      'recruiter_id',
      'fair_id',
      'company_id'
    );

}
