<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateTest extends Model
{
    protected $fillable = [
        'candidate_id',
        'fair_id',
        'test_id',
        'answer_id',
    ];
}
