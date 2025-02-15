<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{

    public function companyDetail()
    {
      return $this->hasOne('App\Company',  'id', 'company_id');
    }
         
    protected $fillable = [
      'user_id',
      'company_name',
      'company_id',
      'credits',
      'reg_notification',
      'enable_exhibitor',
      'user_info',
      'user_title',
      'phone',
      'location',
      'linkedin_profile_link',
      'match_persantage',
      'public_email',
      'show_email',
      'job_email',
      'recruiter_img',
      'allow_schedule',
      'scheduling_percentage',
      'user_image',
      'user_cv',
      'fair_id',
      'user_country',     
      'user_city',
      'user_postal_code',
      'user_timezone'
    ];


}
