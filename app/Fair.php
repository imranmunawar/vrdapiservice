<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fair extends Model
{
    public function organizer()
    {
        return $this->hasOne('App\User', 'id', 'organiser_id' );
    }

    public function organizerDetail()
    {
        return $this->hasOne('App\UserSettings', 'user_id', 'organiser_id' );
    }

    public function fairSetting()
    {
        return $this->hasOne('App\FairSetting', 'fair_id', 'id');
    }

    public function cometChatPro()
    {
        return $this->hasOne('App\CometChatPro', 'fair_id', 'id');
    }

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
        'total_hall',
        'back_scheduling',
        'front_scheduling',
        'scheduling_plugin',
        'layout',
        'presenter',
        'stand_receptionist',
    ];

    public function setting()
    {
        return $this->hasOne('App\FairSetting','fair_id','id');
    }

    public static function fairByShortname($shortName){
        return $this::where('short_name',$short_name)->with('organizer')->first();
    }
}
