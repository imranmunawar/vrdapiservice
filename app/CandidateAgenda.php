<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateAgenda extends Model
{
    
    protected $fillable = [
        'fair_id',
        'candidate_id',
        'webinar_id',
        'webinar_type',
    ];
}
