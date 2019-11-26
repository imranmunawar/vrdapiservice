<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\User;
use App\MatchJob;
use App\MatchWebinar;
use App\CareerTest;
use App\CareerTestAnswer;
use App\UserSettings;
use App\Role;
use App\CandidateTest;
use App\CandidateJob;
use App\MatchRecruiter;

trait CandidatePersonalAgenda
{

  public function getCandidateTestAnswers($candidate_id,$fair_id){
    $data = [];
    $careerTest = CareerTest::all()->where('fair_id',$fair_id); 
    foreach ($careerTest as $key => $value) {
      $data[] = [
        'test'=>$value->question,
        'answers'=> $this->getAnswers($candidate_id,$fair_id,$value->id)
      ];
    }
    return $data;
  }

  public function getAnswers($candidate_id,$fair_id,$test_id){
    $answers = [];
    $candidateTest = CandidateTest::all()->where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->where('test_id',$test_id);
    foreach ($candidateTest as $key => $value) {
      $selectedAnswer = CareerTestAnswer::where('id',$value->answer_id)->first();
      array_push($answers,$selectedAnswer->answer);
    }
    return $answers;
  }

  public function getAnswer($test_id){
    $answers = CareerTestAnswer::where('test_id',$test_id);
    return $answer; 
  }
  // Generate Candidate Personal Agenda
  public function getCandidatePersonalAgenda($request){
	  $fair_id      = $request->fair_id;
    $company_id   = $request->company_id;
    $candidate_id = $request->candidate_id;
    $candidateinfo = $this->show($candidate_id);
    $jobs          = $this->getMatchingJobs($request, 'true');
    $webinars      = $this->getMatchingWebinars($request);
    $testAnswers   = $this->getCandidateTestAnswers($candidate_id, $fair_id);
    $isCandidateInMainHall = User::isCandidateInMainHall($fair_id,$candidate_id);
    $data = [
      'candidateinfo'=> $candidateinfo,
      'jobs'         => $jobs,
      'webinars'     => $webinars,
      'testAnswers'  => $testAnswers,
      'is_candidate_in_hall' => $isCandidateInMainHall
    ];
    return $data;
  }
}
