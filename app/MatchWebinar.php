<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MatchWebinar extends Model
{
	public function companyWebinar()
    {
        return $this->hasOne('App\CompanyWebinar',  'id', 'webinar_id');
    }

	public function companyDetail()
    {
        return $this->hasOne('App\Company',  'id', 'company_id');
    }

    protected $fillable = array(
    	'webinar_id',
        'recruiter_id',
        'candidate_id',
        'company_id',
        'fair_id',
        'percentage',
    );
}
