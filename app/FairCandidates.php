<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FairCandidates extends Model
{
    public function candidate()
    {
        return $this->hasOne('App\User',  'id', 'candidate_id')->select('id','name','email');
    }

    public function candidateInfo()
    {
        return $this->hasOne('App\UserSettings',  'user_id', 'candidate_id')->select('user_id','phone','user_cv','user_country','user_city','user_cv');
    }

    public function candidateTest()
    {
        return $this->hasOne('App\CandidateTest',  'candidate_id', 'candidate_id')->select('candidate_id');
    }

    public function candidateTurnout()
    {
        return $this->hasOne('App\CandidateTurnout',  'candidate_id', 'candidate_id')->select('candidate_id');
    }

    protected $fillable = array(
        'candidate_id',
        'fair_id',
        'status',
        'agenda',
        'presenter',
        'marketing_channel',
        'source',
		'mainhall',
		'email_notification'
    );
}
