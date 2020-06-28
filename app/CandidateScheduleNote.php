<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateScheduleNote extends Model
{
    public function userInfo(){
        return $this->hasOne('App\User',  'id', 'candidate_id');
    }
    protected $fillable = array(
        'slot_id',
        'candidate_id',
        'recruiter_id',
        'cancel_by',
        'notes'
     );
}
