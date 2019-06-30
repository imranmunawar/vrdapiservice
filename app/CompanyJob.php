<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyJob extends Model
{
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
