<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\Fair;
use App\FairCandidates;
use App\MatchJob;
use App\EmailNotification;
use Illuminate\Support\Facades\Mail;

trait FairEndEmailCandidates 
{
  public function fairEndNotification($fair_id){
    $fair = Fair::find($fair_id);
    $fair_id = $fair->id;
    if(!EmailNotification::where('fair_id','=', $fair->fair_id)->where('notification_type','=', 'Fair End')->exists()){
        $fair_date = $fair->end_time;
        $fair_candidates = FairCandidates::where('fair_id',$fair_id)->where('candidate_id','6')->get();
        foreach ($fair_candidates as $key => $fairCandidate) {
            $name  = $fairCandidate->candidate->name;
            $email = $fairCandidate->candidate->email;
            $fair_name = $fair->name;
            $fair_id = $fair->short_name;
            if(MatchJob::where('candidate_id','=', $fairCandidate->candidate_id)->exists()){
                // echo "yesy exits"; die;
                $jobs = MatchJob::where('candidate_id','=', $fairCandidate->candidate_id)->orderBy('percentage', 'Desc')->get();
                Mail::send('emails.fairEndEmailCandidates', ['name' => $name, 'email' => $email, 'jobs' => $jobs, 'fair_name' => $fair_name, 'fair_id' => $fair_id], function($message) use ($email,$name,$fair_name)
                {
                    $message->to($email, $name)->subject($fair_name.' Fair is live.');
                });
            }
        }
        die;
        EmailNotification::create(array(
            'fair_id' => $fair->id,
            'notification_type' => 'Fair End'
        ));
        $data["email_exists"] = true;
    }
        return "Success";
  }
  
}
