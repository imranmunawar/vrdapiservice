<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CandidateTest extends Model
{
	public function options()
    {
        return $this->hasMany('App\CareerTestAnswer', 'test_id', 'test_id' );
    }
	public function test()
	{
	    return $this->hasOne('App\CareerTest', 'id', 'test_id' );
	}
	public function optSelected()
	{
	    return $this->hasOne('App\CareerTestAnswer', 'id', 'answer_id' );
	}
    protected $fillable = [
        'candidate_id',
        'fair_id',
        'test_id',
        'answer_id',
    ];
}
