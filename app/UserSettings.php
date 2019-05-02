<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
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
      'linkedin_profile_link'
    ];


}
