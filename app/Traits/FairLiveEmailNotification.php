<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\Fair;
use App\FairCandidates;
use App\EmailNotification;
use Illuminate\Support\Facades\Mail;

trait FairLiveEmailNotification 
{
  public function fairLiveNotification($fair_id){
    $count    = 0;
    $fairID   = $fair_id;
    $fair     = Fair::find($fairID);
    $fair_date = $fair->start_time;
    $fair_id   = $fair->id;
        EmailNotification::create(array(
            'fair_id' => $fairID,
            'notification_type' => 'Fair Live'
        ));
        $fair_candidates = FairCandidates::where('fair_id',$fair_id)->where('status','Active')->where('email_notification','1')->get();
        foreach ($fair_candidates as $key => $fairCandidate) {
            try {
                ++$count;
                $candidate_id = $fairCandidate->candidate_id;
                $faircandidate_id = $fairCandidate->fair_id;
                $name  = $fairCandidate->candidate->name;
                $email = $fairCandidate->candidate->email;
                $fair_name = $fair->name;
                $fair_id = $fair->short_name;

                $endtime = date('h:ia', strtotime($fair->end_time))." (".$fair->timezone.")";
                Mail::send('emails.fairLiveNotification', ['name' => $name, 'email' => $email, 'faircandidate_id' => $faircandidate_id, 'candidate_id' => $candidate_id, 'fair_name' => $fair_name, 'fair_id' => $fair_id, 'endtime' => $endtime], function($message) use ($email,$name,$fair_name)
                {
                    $message->to($email, $name)->subject($fair_name.' is live on Virtual Recruitment Days');
                });
            } catch (\Exception $e) {
                echo $email." - ".$e->getMessage().' '.$e->getLine();
                $emails_arr .= $email.",";
            }
        }
        $data["email_exists"] = true;
        echo "</br>".$count."</br>";
        return "End of the code";
  }
  
}
