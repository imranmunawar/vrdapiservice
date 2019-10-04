<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\CompanyJob;
use App\JobQuestionnaire;
use App\CareerTest;
use App\CareerTestAnswer;
use App\CandidateTest;
use App\MatchJob;

trait MatchingJobs
{
  // Generate Candidate Matching Jobs
  public function generateMatchingJobs($candidate_id,$fair_id){
  	 // Get All Fair Company Jobs
    MatchJob::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->delete();
  	 $jobs = CompanyJob::where('fair_id',$fair_id)->get();
        $score= 0;
        foreach ($jobs as $job) {
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
                        $jobCriteria = JobQuestionnaire::where('job_id',$job->id)
                                                  ->where('test_id',$question->id)
                                                  ->where('answer',$candidate_question->answer_id)
                                                  ->first();
                        if(!empty($jobCriteria)){
                            if ($jobCriteria->score == 5) {
                               $data["$questionloop"] = $candidate_questions->count()*5; 
                               break;
                            }else{
                                $data["$questionloop"] = $data["$questionloop"] +  $jobCriteria->score;
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
            if($percentage >= $job->match){
                MatchJob::create(array(
                  'job_id'       => $job->id,
                  'candidate_id' => $candidate_id,
                  'company_id'   => $job->company_id,
                  'fair_id'      => $fair_id,
                  'percentage'   => number_format($percentage),
                  'recruiter_id' => $job->recruiter_id
                ));
      				
      			}
            // echo $percentage."<br>";
            $score = 0; 
    }
  }
}
