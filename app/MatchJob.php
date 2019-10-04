<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchJob extends Model {

    public function jobDetail()
    {
        return $this->hasOne('App\CompanyJob',  'id', 'job_id');
    }

    public function companyDetail()
    {
        return $this->hasOne('App\Company',  'id', 'company_id');
    }

    public function candidateJobs()
    {
        return $this->hasOne('App\CandidateJob',  'job_id', 'job_id');
    }
    
    protected $fillable = array(
        'job_id',
        'candidate_id',
        'company_id',
        'fair_id',
        'percentage',
        'recruiter_id'
    );
}
