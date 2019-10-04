<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\CompanyJob;
use App\RecruiterQuestionnaire;
use App\CareerTest;
use App\CareerTestAnswer;
use App\CandidateTest;
use App\MatchRecruiter;
use App\User;

trait MatchingRecruiters
{
  // All Users That have role Recruiter and attached that fair 
  public function getFairRecruiters($fair_id){
    $recruiters = User::whereHas('roles', function ($query) {
        $query->where('name', '=', 'Recruiter');
    })->with('userSetting')->get();
    $userArrs = json_decode(json_encode($recruiters), true);
    $recruiters = array_filter($userArrs, function ($item) use ($fair_id) {
      if ($item['user_setting']['fair_id'] == $fair_id) {
        return true;
      }
        return false;
    });

    return $recruiters;
  }
  // Generate Candidate Matching Recruiters
  public function generateMatchingRecruiters($candidate_id,$fair_id){
  	// Get Fair Recruiters
    MatchRecruiter::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->delete();
	  $recruiters = $this->getFairRecruiters($fair_id);
    $score= 0;
    foreach ($recruiters as $recruiter) {
    	// Get All Career Test Questions
        $questions = CareerTest::where('fair_id',$fair_id)->get();
        foreach ($questions as $question) {
            $questionloop = "question".$question->id;
            $data["$questionloop"] = 0;
            // Get Candidate Questions And Their Selected Answers
            $candidate_questions = CandidateTest::where('test_id',$question->id)
                                              ->where('candidate_id',$candidate_id)
                                              ->where('fair_id', $fair_id)
                                              ->get();
            foreach ($candidate_questions as $candidate_question) {
            		/* Get All Recruiter Attched Questionnaire */
                    $recruiterCriteria = RecruiterQuestionnaire::where('recruiter_id',$recruiter['id'])
                                              ->where('test_id',$question->id)
                                              ->where('answer',$candidate_question->answer_id)
                                              ->first();
                    if(!empty($recruiterCriteria)){
                        if ($recruiterCriteria->score == 5) {
                           $data["$questionloop"] = $candidate_questions->count()*5; 
                           break;
                        }else{
                            $data["$questionloop"] = $data["$questionloop"] +  $recruiterCriteria->score;
                        }
                    }else{
                        $data["$questionloop"] = $data["$questionloop"] +  0;
                    }       
            }
            // Score Calculating Formula
            $score = $score + number_format(($data["$questionloop"]/($candidate_questions->count()*5)*100));
          
        } 
        // Actual Matched Job Percentage 
        $percentage = $score/$questions->count();
        if($percentage >= $recruiter['user_setting']['match_persantage']){
            MatchRecruiter::create(array(
              'recruiter_id' => $recruiter['id'],
              'candidate_id' => $candidate_id,
              'company_id'   => $recruiter['user_setting']['company_id'],
              'fair_id'      => $fair_id,
              'percentage'   => number_format($percentage),
            ));
  				
  			}
        // echo $percentage."<br>";
        $score = 0; 
    }
  }
}
