<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyJob extends Model
{
    public function applicationsCount()
    {
        return $this->hasMany('App\CandidateJob','job_id','id');

    }

    public function company()
    {
        return $this->hasOne('App\Company','id','company_id');

    }

    protected $fillable = [
        'fair_id',
        'company_id',
        'title',
        'description',
        'job_type',
        'language',
        'recruiter_id',
        'location',
        'contact_name',
        'phone',
        'email',
        'url',
        'salary',
        'match',
        'status'
    ];
}
