<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingRegistration extends Model
{
	public function candidate()
    {
        return $this->hasOne('App\User',  'id', 'user_id')->select('id','name','email');
    }

    public function candidateInfo()
    {
        return $this->hasOne('App\UserSettings',  'user_id', 'user_id')->select('user_id','user_country','user_city','user_cv','phone');
    }

    public function candidateFairInfo()
    {
        return $this->hasOne('App\FairCandidates',  'candidate_id', 'user_id');
    }

    public function candidateCareerTest()
    {
        return $this->hasOne('App\CandidateTest',  'candidate_id', 'user_id');
    }

    public function candidateTurnout()
    {
        return $this->hasOne('App\CandidateTurnout',  'candidate_id', 'user_id')->select('candidate_id');
    }


    protected $fillable = [
       'user_id',
       'channel_id'
    ];
}
