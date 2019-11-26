<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgendaView extends Model
{
    public function candidate()
    {
        return $this->hasOne('App\User',  'id', 'candidate_id');
    }

    public function candidateSetting()
    {
        return $this->hasOne('App\UserSetting',  'user_id', 'candidate_id');
    }

    protected $fillable = array(
        'recruiter_id',
        'candidate_id',
        'fair_id',
        'company_id',
        'percentage',
        'shortlisted',
		'rejected',
		'notes'
    );


}
