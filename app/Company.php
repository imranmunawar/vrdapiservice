<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'admin_id',
        'fair_id',
        'company_name',
        'company_email',
        'company_post_code',
        'company_state',
        'company_country',
        'company_match',
        'company_web_url',
        'company_facebook_url',
        'company_youtube_url',
        'company_twitter_url',
        'company_in_url',
        'company_instagram_url',
        'company_stand_type',
        'description',
        'company_logo',
        'company_stand_image',
    ];

    public function stand()
    {
        return $this->hasOne('App\CompanyStand');
    }

    public function media(){
        return $this->hasMany('App\CompanyMedia');
    }

}
