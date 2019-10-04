<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\CompanyWebinar;
use App\WebinarQuestionnaire;
use App\CareerTest;
use App\CareerTestAnswer;
use App\CandidateTest;
use App\MatchWebinar;

trait MatchingWebinars
{
  // Generate Candidate Matching Jobs
  public function generateMatchingWebinars($candidate_id,$fair_id){
  	 // Get All Fair Company Jobs
    MatchWebinar::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->delete();
  	 $webinars = CompanyWebinar::where('fair_id',$fair_id)->get();
        $score= 0;
        foreach ($webinars as $webinar) {
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
                		/* Get All Job Attched Questionnaire */
                        $webinarCriteria = WebinarQuestionnaire::where('webinar_id',$webinar->id)
                                                  ->where('test_id',$question->id)
                                                  ->where('answer',$candidate_question->answer_id)
                                                  ->first();
                        if(!empty($webinarCriteria)){
                            if ($webinarCriteria->score == 5) {
                               $data["$questionloop"] = $candidate_questions->count()*5; 
                               break;
                            }else{
                                $data["$questionloop"] = $data["$questionloop"] +  $webinarCriteria->score;
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
            if($percentage >= $webinar->match){
                MatchWebinar::create(array(
                  'webinar_id'   => $webinar->id,
                  'candidate_id' => $candidate_id,
                  'company_id'   => $webinar->company_id,
                  'fair_id'      => $fair_id,
                  'percentage'   => number_format($percentage),
                ));
      				
      			}
            // echo $percentage."<br>";
            $score = 0; 
    }
  }
}
