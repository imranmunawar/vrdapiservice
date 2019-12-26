<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\Fair;
use App\User;
use App\FairCandidates;
use App\CandidateAgenda;
use App\CompanyJob;
use Illuminate\Support\Facades\Mail;

trait CandidateEmail 
{
  public function generateEmail($request,$candidate_id){
    $fair = Fair::find($request->fair_id);
    date_default_timezone_set($request->timezone);
    //start time of fair
    $start_date = new \DateTime($fair->start_time, new \DateTimeZone($fair->timezone));
    $start_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    //End time of fair
    $end_date = new \DateTime($fair->end_time, new \DateTimeZone($fair->timezone));
    $end_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $time = $start_date->format('h:ia')." ".$start_date->format('d M Y');
    $calendar_time = $start_date->format('Ymd')."T".$start_date->format('His')."/".$end_date->format('Ymd')."T".$start_date->format('His');
    $candidate_id  = $candidate_id;
    $name          = $request->name;
    $email         = $request->email;
    $password      = $request->password;
    $url           = $fair->short_name;
    $fairname      = $fair->name;
    $faircandidate_id  = $request->fair_id;
    $candidateLoginUrl = env('FRONT_URL').'/'.$fair->short_name.'/home';
    Mail::send('emails.candidate', 
        [
            'name'  => $name, 
            'email' => $email, 
            'faircandidate_id' => $faircandidate_id, 
            'candidate_id'     => $candidate_id, 
            'url'      => $url, 
            'candidateLoginUrl'=>$candidateLoginUrl,
            'fairname' => $fairname, 
            'time'     => $time, 
            'password' => $password, 
            'calendar_time' => $calendar_time
        ], function($message) use ($email,$name,$url,$time,$password, $fairname, $calendar_time)
    {
        $message->to($email, $name)->subject('Welcome to '.$fairname);
    });

    if($fair->organizer){
        if($fair->organizerDetail){
            if($fair->organizerDetail->reg_notification == 1){
                $loginUrl =  env('BACKEND_URL').'/login';
                $email = $fair->organizer->email;
                $organizer_name = $fair->organizer->name;
                Mail::send('emails.organizerNotification', 
                    [
                        'name'           => $name, 
                        'organizer_name' => $organizer_name, 
                        'email'    => $request->email, 
                        'url'      => $url, 
                        'fairname' => $fairname, 
                        'time'     => $time,
                        'loginUrl' => $loginUrl

                    ], function($message) use ($email,$name,$url,$time, $fairname, $organizer_name)
                    {
                        $message->to($email, $organizer_name)->subject('New Candidate has registered to '.$fairname);
                    });
            }
        }
    }
  }


  public function jobApplyEmail($request)
  {
    $job = CompanyJob::find($request->job_id);
    $recruiter = User::find($job->recruiter_id);
    $candidate = User::find($request->candidate_id);
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

    public function blockCandidateEmail($fair_id,$candidate_id){
        $candidate = User::find($candidate_id);
        $name  =  $candidate->name;
        $email = $candidate->email;
        $fair  = Fair::find($fair_id);
        $fairname = $fair->name;
        $candidate_id = $candidate->id;
        $emails = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->first();
        $faircandidate_id = $emails->id;
        if($emails->email_notification == 1){
            Mail::send('emails.blockCandidate', ['name' => $name, 'email' => $email, 'faircandidate_id' => $faircandidate_id, 'candidate_id' => $candidate_id, 'fairname' => $fairname], function($message) use ($email,$name, $fairname)
            {
                $message->to($email, $name)->subject('Thank you for your interest in the '.$fairname);
            });
            Session::flash('success', 'Candidate blocked successfully');
        }
    }

    public function unsubscribe($fair_id,$candidate_id){
       $updateEmailNotifaction = FairCandidates::where('id', $fair_id)->where('candidate_id', $candidate_id)->update(array('email_notification' => '0'));
       return view('emails.unsubscribe');
    }
  
}
