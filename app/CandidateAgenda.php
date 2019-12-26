<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateAgenda extends Model
{
    public function candidate()
    {
        return $this->hasOne('App\User',  'id', 'candidate_id');
    }
    protected $fillable = [
        'fair_id',
        'candidate_id',
        'webinar_id',
        'webinar_type',
    ];
}
