<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fair extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'short_name',
        'email',
        'phone',
        'timezone',
        'register_time',
        'start_time',
        'end_time',
        'website',
        'facebook',
        'twitter',
        'linkedin',
        'youtube',
        'instagram',
        'organiser_id',
        'fair_image',
        'fair_video',
        'fair_mobile_image',
        'fair_type',
        'fair_status',
        'chat_status',
        'layout',
        'presenter',
        'stand_receptionist'
    ];

    public function organizer()
    {
        return $this->belongsTo('App\User', 'organiser_id','id');
    }

    public function setting()
    {
        return $this->hasOne('App\FairSetting','fair_id','id');
    }

    public static function fairByShortname($shortName){
        return $this::where('short_name',$short_name)->with('organizer')->first();
    }
}
