<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateTurnout extends Model
{
    protected $fillable = [
        'candidate_id','fair_id',
    ];
}
