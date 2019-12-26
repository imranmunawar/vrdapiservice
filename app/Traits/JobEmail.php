<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\CompanyJob;
use App\User;
use App\FairCandidates;
use App\CandidateAgenda;
use Illuminate\Support\Facades\Mail;

trait JobEmail 
{
  public function jobApplyEmail($request)
  {
    $job = CompanyJob::find($request->job_id);
    $recruiter = User::find($job->recruiter);
    $candidate = User::find($request->candidate);
    $data["name"]         = $candidate->name;
    $data["job_title"]    = $job->title;
    $data["fairname"]     = $job->company->name;
    $data["company_name"] = $job->company->name;
    $data["candidate_id"] = $candidate->id;
    $data["email"]        = $candidate->email;
    $data["recruiter_name"]  = $recruiter->name;
    $data["recruiter_email"] = $recruiter->email;
    $data["percentage"]   = $request->percentage;
    $data["candidate_id"] = $candidate->id;
    $emails = FairCandidates::where('candidate_id',$candidate->id)->where('fair_id',$request->fair_id)->first();
    $data["faircandidate_id"] = $emails->id;
    if($emails->email_notification == 1){
    Mail::send('emails.candidateJobApply', ['data' => $data], function($message) use ($data)
        {
            $message->to($data["email"], $data["name"])->subject('Thanks for applying on '.$data["job_title"].' Job');
        });
    }
    // if($data["percentage"] >= 50 && $recruiter->recruiter->job_email == 1){
    //     Mail::send('emails.candidate-job-apply-recruiter-email', ['data' => $data], function($message) use ($data)
    //         {
    //             $message->to($data["recruiter_email"], $data["recruiter_name"])->subject($data["name"].' applied on '.$data["job_title"].' Job');
    //         });
    // }
   } 
}
