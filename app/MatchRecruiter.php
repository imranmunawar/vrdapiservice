<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchRecruiter extends Model
{
	public function recruiter()
    {
        return $this->hasOne('App\User', 'id', 'recruiter_id');
    }
    public function companyDetail()
    {
        return $this->hasOne('App\Company',  'id', 'company_id');
    }
    public function recruiterSetting()
    {
        return $this->hasOne('App\UserSettings',  'user_id', 'recruiter_id');
    }

    public function candidate()
    {
        return $this->hasOne('App\User', 'id', 'candidate_id');
    }
    public function candidateSetting()
    {
        return $this->hasOne('App\UserSettings',  'user_id', 'candidate_id');
    }

    protected $fillable = array(
        'recruiter_id',
        'candidate_id',
        'company_id',
        'fair_id',
        'percentage',
    );
}
