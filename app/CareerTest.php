<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CareerTest extends Model
{
     protected $fillable = [
        'admin_id',
        'fair_id',
        'question',
        'short_question',
        'backoffice_question',
        'question_type',
        'min_selection',
        'max_selection',
        'display_order'
    ];

    public function answers()
    {
        return $this->hasMany('App\CareerTestAnswer', 'test_id','id');
    }

    public static function storeCareerTest($request){
        CandidateTest::where('candidate_id',$request->candidate_id)->where('fair_id',$fair_id)->delete();
        $answers = $request->selectedAnswers;
        $fair_id = $request->fair_id;
        $candidate_id = $request->candidate_id;
        foreach ($answers as $key => $row) {
            CandidateTest::create([
                'candidate_id'=> $candidate_id,
                'fair_id'     => $fair_id,
                'test_id'     => $this->getTestId($key),
                'answer_id'   => $key
            ]);   
        }

        $this->generateMatchingJobs($candidate_id,$fair_id);
    }


}
