<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\CompanyWebinar;
use App\CandidateAgenda;
use Illuminate\Support\Facades\Mail;

trait WebinarEmail 
{
  public function webinarLiveNotification($webinar_id)
  {
    $emails_arr = '';
    $webinar = CompanyWebinar::find($webinar_id);
    $candidates = CandidateAgenda::where('webinar_id', '=', $webinar_id)->where('fair_id', '=', $webinar->fair_id)->get();
    $fair_id = $webinar->fair->short_name;
    $webinar = $webinar->title;
    $candidateLoginUrl = env('FRONT_URL').'/'.$webinar->fair->short_name.'/home';
    foreach ($candidates as $key => $candidate) {
        try {
            $name = $candidate->candidate->name;
            $email = $candidate->candidate->email;
            Mail::send('emails.webinarLiveNotification', 
                [
                    'name'             => $name, 
                    'email'            => $email, 
                    'webinar'          => $webinar, 
                    'fair_id'          => $fair_id,
                    'candidateLoginUrl'=> $candidateLoginUrl
                ], function($message) use ($email,$name,$webinar)
            {
                $message->to($email, $name)->subject($webinar.' is started now on Virtual Recruitment Days');
            });
        } catch (\Exception $e) {
            echo $email." - ".$e->getMessage().' '.$e->getLine();
            $emails_arr .= $email.",";
        }
    }
    // Session::flash('success', 'Webinar Email notifications successfully sent to all candidates.');
    return Redirect::back();
 } 
}
