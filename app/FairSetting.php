<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FairSetting extends Model
{
    protected $fillable = [
        'fair_id',
        'information_text',
        'offline_text',
        'address',
        'terms_conditions',
        'privacy_policy',
        'fair_news',
        'webinar_enable',
        'cv_required',
        'interview_room',
        'seminar',
        'video_chat',
        'user_vetting',
        'limited_access',
        'chat_status'
    ];
}
