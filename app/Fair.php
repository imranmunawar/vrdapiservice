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
        'presenter_id',
        'organiser_id',
        'receptionist_id',
        'fair_image',
        'fair_video',
        'fair_type',
        'status'
    ];

    public function organizer()
    {
        return $this->belongsTo('App\User', 'organiser_id','id');
    }
}
