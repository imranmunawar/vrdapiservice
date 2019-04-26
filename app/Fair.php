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
        'website',
        'presenter_id',
        'organiser_id',
        'receptionist_id',
        'fair_image',
        'fair_video',
        'fair_type',
        'status'
    ];

    public function sociallink()
    {
        return $this->belongsToMany(Sociallinks::class)->withPivot('link_url');
    }

}