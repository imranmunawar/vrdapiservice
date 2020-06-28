<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Auth;
use Session;
use AppHelper;
use DateTime;
use DateTimeZone;
use App\Fair;
use App\User;
use App\Company;
use App\UserSettings;
use App\FairCandidates;
use App\RecruiterSchedule;
use App\RecruiterScheduleInvite;
use App\RecruiterScheduleBooked;
use App\CandidateScheduleNote;
use Spatie\CalendarLinks\Link;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Traits\ZoomMeetings;


class RecruiterSchedulingController extends Controller {
	use ZoomMeetings;

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $req)
	{
		$dataArr = [];
		$start_date    = $req->start_date;
		$end_date      = $req->end_date;
		$status        = $req->status;

		if($status != 'all' ) {
		  $schedules = RecruiterSchedule::where('fair_id',$req->fair_id)
		    ->where('company_id',$req->company_id)
		    ->where('recruiter_id',$req->recruiter_id)
		    ->where('status',$status)
		    ->orderBy("created_at",'DESC')
		    ->get();
		}else{
			$schedules = RecruiterSchedule::where('fair_id',$req->fair_id)
		    ->where('company_id',$req->company_id)
		    ->where('recruiter_id',$req->recruiter_id)
		    ->orderBy("created_at",'DESC')
		    ->get();
		}


		if (!empty($start_date) && !empty($end_date)) {
			$filtered  = $schedules->filter(function ($value, $key) use ($start_date,$end_date) {
				$slotDate   = date("d-m-Y",strtotime($value->days));
				$start_date = date("d-m-Y",strtotime($start_date));
				$end_date   = date("d-m-Y",strtotime($end_date));
			    if ($slotDate >= $start_date && $slotDate  <= $end_date) {
			        return true;
			    }
			});
			$schedules = $filtered->all();
		}

		if ($schedules) {
			foreach ($schedules as $key => $row) {
				$dataArr[] = [
				  'id'               => $row->id,
				  'fair_id'          => $row->fair_id,
			      'company_id'       => $row->company_id,
			      'recruiter_id'     => $row->recruiter_id,
			      'recruiter_name'   => $row->RecruiterDetails->name,
			      'candidate_id'     => $row->candidate_id,
			      'start_time'       => date('h:i A', strtotime($row->start_time)),
			      'end_time'         => date('h:i A', strtotime($row->end_time)),
			      'days'             => Carbon::createFromFormat('d-m-Y',$row->days)->format('F j, Y'),
			      'days_arr'         => $row->days_arr,
			      'slotStatus'       => $row->status
				];
			}
		}

		return $dataArr;
	}

	public function slotStatus($slot_id,$fair_id,$recruiter_id){
		$slot = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('slot_id',$slot_id)->select('status')->first();
		if ($slot) {
			return $slot->status;
		}

		return 'invite';
	}

	public function scheduledInterviews(Request $req)
	{
		$fair_id      = $req->fair_id;
		$recruiter_id = $req->recruiter_id;
		$company_id   = $req->company_id;

		$schedules  = RecruiterSchedule::where('fair_id',$fair_id)->where('company_id',$company_id)->where('recruiter_id',$recruiter_id)->get();
		$interviews = RecruiterScheduleBooked::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->orderBy('date', 'ASC')->orderBy('start_time', 'ASC')->get();

		$interview_arr = array();
		foreach ($schedules as $key => $schedule) {
			$day = $schedule->days;
			if(!RecruiterScheduleBooked::where('start_time',$schedule->start_time)->where('end_time',$schedule->end_time)->where('date',$day)->where('recruiter_id',$schedule->recruiter_id)->exists()){
				if ($this->isSlotNotInvited($schedule->id,$schedule->fair_id,$schedule->recruiter_id) == 'false') {
					$interview_arr[] = array(
						"id"   => $schedule->id,
						"name" => "Invited: ".$schedule->start_time.' - '.$schedule->end_time,
			    		"startdate" => $day,
			    		"enddate"   => $day,
			    		"starttime" => $schedule->start_time,
			    		"endtime"   => $schedule->end_time,
			    		"color"     => "#1976d2",
			    		"url"       => "",
			    		'title'     => 'Delete Slot'
					);
				}else{
					$interview_arr[] = array(
						"id"   => $schedule->id,
						"name" => $schedule->start_time.' - '.$schedule->end_time.' <i class="fas fa-times-circle pull-right deleteSlotIcon" onclick="deleteSlot('.$schedule->id.')" title="asdasdasd"></i>',
			    		"startdate" => $day,
			    		"enddate"   => $day,
			    		"starttime" => $schedule->start_time,
			    		"endtime"   => $schedule->end_time,
			    		"color"     => "#37BC9B",
			    		"url"       => "",
			    		'title'     => 'Delete Slot'
					);
				}
			}
		}
		// $interview_arr = array();
		foreach ($interviews as $key => $interview) {
			$interview_arr[] = array(
				"id" => $interview->id,
				"name" => "Meeting: ".$interview->CandidateDetails->name,
	    		"startdate" => $interview->date,
	    		"enddate"   => $interview->date,
	    		"starttime" => $interview->start_time,
	    		"endtime"   => $interview->end_time,
	    		"color"     => "#DA4453",
	    		"url"       => env('BACKEND_URL').'fair/candidate/detail/'.$interview->candidate_id.'/'.$interview->u_id
			);
		}

		return $interview_arr;
	}


	public function getCandidateNotes($candidate_id,$recruiter_id){
		$notes = [];
		$candidateNotes = CandidateScheduleNote::where('candidate_id',$candidate_id)->where('recruiter_id',$recruiter_id)->get();
		if ($candidateNotes) {
			foreach ($candidateNotes as $key => $row) {
				$date = Carbon::parse($row->created_at)->format('F j, Y h:i A');
				$name = $row->userInfo->name;
				if ($row->cancel_by == 1) {
					$name  = User::where('id',$row->recruiter_id)->first()->name;
				}
				$notes[] = [
					'id'      => $row->id,
					'slot_id' => $row->slot_id,
					'date'    => $date,
					'note'    => $row->notes,
					'name'    => $name,
					'role'    => $row->cancel_by == 0 ? 'Candidate':'Recruiter'
				];
			}
		}

		return $notes;
	}

	public function candidateTimezone($id){
	   $candidate = UserSettings::where('user_id',$id)->select('user_timezone')->first();
	   return $candidate->user_timezone;
	}

	public function fairTimezone($id){
	   $fair = Fair::where('id',$id)->select('timezone')->first();
	   return $fair->timezone;
	}

	public function getRecruiterCompany($id){
	   $company = Company::where('id',$id)->select('company_name')->first();
	   if ($company) {
	   	 return $company->company_name;
	   }
	   return '';
	}

	public function interviewInvitations(Request $req){
		$interview_arr = array();
		$fair_id       = $req->fair_id;
		$recruiter_id  = $req->recruiter_id;
		$candidate_id  = $req->candidate_id;
		$company_id    = $req->company_id;
		$start_date    = $req->start_date;
		$end_date      = $req->end_date;
		$slotStatus    = $req->status;
		$request_by    = $req->request_by;
		$fairTimezone  = $this->fairTimezone($fair_id);

		if (!empty($candidate_id)) {
			$candidateTimezone = $this->candidateTimezone($candidate_id);
		}

		if ($slotStatus != 'all') {
			if ($request_by == 'recruiter') {
			   $interviews = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status',$slotStatus)->orderBy('created_at', 'DESC')->get();	
			}
			if ($request_by == 'candidate') {
			   $interviews = RecruiterScheduleInvite::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->where('status',$slotStatus)->orderBy('created_at', 'DESC')->get();	
			}
		}else{
          
            if ($request_by == 'recruiter') {
			  $interviews = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->orderBy('created_at', 'DESC')->get();	
			}
			if ($request_by == 'candidate') {
			   $interviews = RecruiterScheduleInvite::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->orderBy('created_at', 'DESC')->get();
			}
		}
		if (!empty($start_date) && !empty($end_date)) {
			// echo "asdasdas"; die;
			$filtered  = $interviews->filter(function ($value, $key) use ($start_date,$end_date) {
				$slotDate   = date("d-m-Y",strtotime($value->SlotInfo->days));
				$start_date = date("d-m-Y",strtotime($start_date));
				$end_date   = date("d-m-Y",strtotime($end_date));
				if ($slotDate >= $start_date && $slotDate  <= $end_date) {
				    return true;
			    }
			});

			$interviews = $filtered->all();
		}

		// $interview_arr = array();
		foreach ($interviews as $key => $interview) {
			if ($request_by == 'recruiter') {
				$date = Carbon::createFromFormat('d-m-Y',$interview['SlotInfo']['days']);
			    $date = $date->englishDayOfWeek.', '.$date->toFormattedDateString().' '.date('h:i A', strtotime($interview->SlotInfo->start_time)).' - '.date('h:i A', strtotime($interview->SlotInfo->end_time));
			}
			if ($request_by == 'candidate') {
				$d1   = $interview['SlotInfo']['days'].$interview->SlotInfo->start_time;
				$date = $this->localDateTime($d1,$candidateTimezone,$fairTimezone);
		        $date = date("F jS, Y", strtotime($date));
			    $date = $date.' '.$this->localTime($interview->SlotInfo->start_time,$candidateTimezone,$fairTimezone).' - '.$this->localTime($interview->SlotInfo->end_time,$candidateTimezone,$fairTimezone);
			}
		
			$interview_arr[] = array(
				'id'        => $interview->id,
				"u_id"      => $interview->u_id,
				'slot_id'   => $interview->slot_id,
				'notes'     => $this->getCandidateNotes($interview->candidate_id,$interview->recruiter_id),
				'status'    => $interview->status,
				"name"      => $request_by == 'recruiter' ? $interview->CandidateDetails->name : $interview->RecruiterDetails->name,
				'email'     => $request_by == 'recruiter' ? $interview->CandidateDetails->email : $interview->RecruiterDetails->email,
				'company'      => $this->getRecruiterCompany($interview->RecruiterUserSetting->company_id),
				'slot'         => $date,
				'invited_by'   => $interview->invited_by,
				'candidate_id' => $interview->candidate_id,
	    		"url"          => env('BACKEND_URL').'fair/candidate/detail/'.$interview->candidate_id
			);
		}

		return $interview_arr;
	}

	public function interviewApproved($interview_id){
		$interview = RecruiterScheduleBooked::find($interview_id);
		if ($interview->is_approved == 0) {
		$interview->update(['is_approved'=>1]);
		$date = $interview->date;
		$u_id = $interview->u_id;
		$start_time    = $interview->start_time;
		$end_time      = $interview->end_time;
		$fair_id       = $interview->fair_id;
		$recruiter_id  = $interview->recruiter_id;
		$candidate_id  = $interview->candidate_id;
			$candidate = User::find($candidate_id);
			$fair = Fair::find($fair_id);
			$fairCandidate = FairCandidates::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->first();
			$faircandidate_id = $fairCandidate->id;
			$candidate_id     = $candidate->id;
			$name  = $candidate->name;
			$candidateName  = $candidate->name;
			$email = $candidate->email;
			$url   = $fair->short_name;
			$frontFairUrl = env('FRONT_URL').$fair->short_name.'/home';
			$cancelUrl = env('BACKEND_URL').'candidate/interview/cancel/'.$u_id;
			$fairname = $fair->name;
			$timezone = $fair->timezone;
			$calendar_time = date('Ymd', strtotime($date))."T".date('His', strtotime($start_time))."/".date('Ymd', strtotime($date))."T".date('His', strtotime($end_time));

			Mail::send('emails.interview-confirm', [
				'name' => $name,
				'email' => $email,
				'faircandidate_id' => $faircandidate_id,
				'candidate_id'     => $candidate_id,
				'url' => $url,
				'fairname' => $fairname,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'date' => $date,
				'u_id' => $u_id,
				'timezone' => $timezone,
				'calendar_time' => $calendar_time,
				'frontFairUrl'  => $frontFairUrl,
				'cancelUrl'     => $cancelUrl
				], function($message) use ($email,$name)
			{
			    $message->to($email, $name)->subject('Congratulation! Your interview is successfully scheduled');
			});


			$recruiter = User::find($recruiter_id);
			$recruiter_id = $recruiter->id;
			$name = $recruiter->name;
			$email = $recruiter->email;

			Mail::send('emails.interview-confirm', [
				'name' => $name,
				'email' => $email,
				'faircandidate_id' => $faircandidate_id,
				'candidate_id'     => $candidate_id,
				'url' => $url,
				'fairname' => $fairname,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'date' => $date,
				'u_id' => $u_id,
				'timezone' => $timezone,
				'calendar_time' => $calendar_time,
				'frontFairUrl'  => $frontFairUrl,
				'cancelUrl'     => $cancelUrl
				], function($message) use ($email,$name)
			{
			    $message->to($email, $name)->subject('Congratulation! Your interview is successfully scheduled');
			});
			return response()->json([
	            'success'    => true,
	            'message' => 'Interview Scheduled With ' .$candidateName. ' Approved Successfully'
	        ],200);
		}

		return response()->json([
            'success'    => fasle,
            'message' => 'Interview Scheduled Approved Not Successfully'
        ],200);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function createSchedule(Request $request)
	{
		// $intervel     = $req->interval;
		// $start_time_interval = $request->start_time;
		// $selectedDays = explode(" - ", $req->days);
		// $date_from    = strtotime($selectedDays[0]); // Convert date to a UNIX timestamp
		// $date_to      = strtotime($selectedDays[1]); // Convert date to a UNIX timestamp
		// $days         = array();
		// for ($i = $date_from; $i <= $date_to; $i+=86400) {
		//     $days[] = date("d-m-Y", $i);
		// }

		// $selectedDays = explode(" - ", $request->days);
		// $date_from = strtotime($selectedDays[0]); // Convert date to a UNIX timestamp
		// $date_to = strtotime($selectedDays[1]); // Convert date to a UNIX timestamp
		$days = $request->days;
		// for ($i = $date_from; $i <= $date_to; $i+=86400) {
		//     $days[] = date("d-m-Y", $i);
		// }


		foreach ($days as $key => $day) {
			$intervel = $request->interval;
			$start_time_interval = $request->start_time;
			$end_time_interval   = $request->start_time;
			$start_time = $request->start_time;
			$end_time   = $request->end_time;
			$end_interval = $request->interval * 2;
			// 90

			$loopCount = 0;
			while ($end_time_interval  < $end_time) {
				    // 12:00
				if($loopCount > 0){
					$start_time_interval = date('h:i A', strtotime("+$intervel minutes", strtotime($start_time_interval)));
				}
				$end_time_interval = date('h:i A', strtotime("+$intervel minutes", strtotime($start_time_interval)));
				$return =  $start_time_interval." - ".$end_time_interval."</br>";
				$loopCount++;
				// if ($loopCount > 5) {
				// 	exit();
				// }
				// echo $return."<br/>";

				if (!RecruiterSchedule::where('fair_id',$request->fair_id)
					->where('company_id',$request->company_id)
					->where('recruiter_id',$request->recruiter_id)
					->where('start_time',$start_time_interval)
					->where('end_time',$end_time_interval)
					->where('days',$day)->exists()){
					$create = RecruiterSchedule::create(array(
						'fair_id'      => $request->fair_id,
						'company_id'   => $request->company_id,
						'recruiter_id' => $request->recruiter_id,
						'start_time'   => $start_time_interval,
						'end_time'     => $end_time_interval,
						'days'         => $day,
						'days_arr'     => json_encode($request->days),
					));
				}
			}
		}

		return response()->json([
            'success' => true,
            'message' => 'Time Slots Added Successfully'
        ],200);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function editSchedule($id)
	{
		$schedule = RecruiterSchedule::find($id);
		return $schedule;

	}
	public function getCandidateSchedule(Request $request)
	{
		$user = User::find($request->login_id);
		$schedule = RecruiterScheduleBooked::where('u_id', '=', $request->u_id)->first();
		if($user && $schedule){
			$data = array(
				'meeting_id' 	=> $schedule->meeting_id,
				'start_url' 	=> $schedule->start_url,
				'join_url' 		=> $schedule->join_url,
				'password' 		=> $schedule->password,
				'name' 		    => $user->name,
				'email' 		=> $user->email,
				'role'          => $user->roles->first()->name == 'Recruiter' ? 1 : 0
			);
			return response()->json($data, 200);
		}else{
				return response()->json([
					 'error' => true,
					 'message' => 'Invalid Data'
				], 404);
		}
	}
	public function getInterviewRecording(Request $request)
	{
		$schedule = RecruiterScheduleBooked::where('u_id', '=', $request->u_id)->first();
		if($schedule){
			$response = $this->getMeetingRecording($schedule->meeting_id);
			return response()->json($response, 200);
		}else{
				return response()->json([
					 'error' => true,
					 'message' => 'Invalid Data'
				], 404);
		}
	}
	public function startInterview($u_id, $login_id)
	{
		$api_key = env('ZOOM_API_KEY');
    $api_sercet = env('ZOOM_API_SECRET');

		$user = User::find($login_id);
		$schedule = RecruiterScheduleBooked::where('u_id', '=', $u_id)->first();
		if($user && $schedule){
			$data = array(
				'meeting_id' 	=> $schedule->meeting_id,
				'start_url' 	=> $schedule->start_url,
				'join_url' 		=> $schedule->join_url,
				'password' 		=> $schedule->password,
				'name' 				=> $user->name,
				'email' 			=> $user->email,
			);

		$meeting_number = $data["meeting_id"];
	    $name = $data["name"];
	    $password = $data["password"];
	    $email = $data["email"];

	    $role = 1;
	    $time = time() * 1000; //time in milliseconds (or close enough)
	  	$data = base64_encode($api_key . $meeting_number . $time . $role);
	  	$hash = hash_hmac('sha256', $data, $api_sercet, true);
	  	$_sig = $api_key . "." . $meeting_number . "." . $time . "." . $role . "." . base64_encode($hash);
	    $_sig = rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
	    return view('zoom.index',['interview_id'=> $meeting_number, 'name'=> $name, 'sig' => $_sig, 'password' => $password, 'email' => $email]);

		}else{
				return response()->json([
					 'error' => true,
					 'message' => 'Invalid Data'
				], 404);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function updateSchedule(Request $req)
	{
		$selectedDays = explode(" - ", $req->days);
		$date_from = strtotime($selectedDays[0]); // Convert date to a UNIX timestamp
		$date_to = strtotime($selectedDays[1]); // Convert date to a UNIX timestamp
		$days = array();
		for ($i = $date_from; $i <= $date_to; $i+=86400) {
				$days[] = date("d-m-Y", $i);
		}
		$update = RecruiterSchedule::where('id',$req->schedule_id)->update(array(
			'recruiter_id' => $req->recruiter_id,
			'start_time'   => $req->start_time,
			'end_time'     => $req->end_time,
			'days'         => $req->days,
			'days_arr'     => json_encode($days),
		));

		if ($update) {
			return response()->json([
            'success' => true,
            'message' => 'Time Slot Updated Successfully'
            ],200);
		}

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function deleteSchedule($id)
 	{
 		$deleteSlot = RecruiterSchedule::destroy($id);
 		$checkInvitedSlot = RecruiterScheduleInvite::where('slot_id',$id)->first();
 		if ($checkInvitedSlot) {
 			$deleteSlot = RecruiterScheduleBooked::where('u_id',$checkInvitedSlot->u_id)->delete();
 		}
        $deleteSlot = RecruiterScheduleInvite::where('slot_id',$id)->delete();
		return response()->json([
        'success' => true,
        'message' => 'Time Slot Deleted Successfully'
        ],200);
 	}

 	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function blukDeleteSlots(Request $request)
 	{
 	  $ids  = $request->ids;
      for ($i=0; $i < count($ids) ; $i++) {
        $deleteSlot       = RecruiterSchedule::destroy($ids[$i]);
        $checkInvitedSlot = RecruiterScheduleInvite::where('slot_id',$ids[$i])->first();
 		if ($checkInvitedSlot) {
 			$deleteSlot = RecruiterScheduleBooked::where('u_id',$checkInvitedSlot->u_id)->delete();
 		}
        $deleteSlot = RecruiterScheduleInvite::where('slot_id',$ids[$i])->delete();
      }
      
      return response()->json([
	    'success' => true,
	    'message' => 'Time Slots Deleted Successfully'
	  ],200);
 	}

 	public function getUniqueId(){
 		return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8).time().substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
 	}

 	public function rcInvitationEmail($emailArr){
 		$cName    = $emailArr['candidate_name'];
 		$cEmail   = $emailArr['candidate_email'];
 		$rName    = $emailArr['recruiter_name'];
 		$rEmail   = $emailArr['recruiter_email'];
 		$fairName = $emailArr['fairname'];

 		Mail::send('emails.scheduling-invitation',$emailArr, function($message) use ($cEmail,$cName, $fairName,$rName){
			$message->to($cEmail, $cName)->subject($rName.' Invited You for the interview at '.$fairName);
		});

		Mail::send('emails.recruiter-scheduling-invitation',$emailArr, function($message) use ($rEmail,$rName, $fairName,$cName){
			$message->to($rEmail, $rName)->subject($cName.' Invited Successfully for the interview at '.$fairName);
		});
 	}

 	public function crInvitationEmail($emailArr){
		$cName    = $emailArr['candidate_name'];
 		$cEmail   = $emailArr['candidate_email'];
 		$rName    = $emailArr['recruiter_name'];
 		$rEmail   = $emailArr['recruiter_email'];
 		$fairName = $emailArr['fairname'];

		Mail::send('emails.crInvitationEmail',$emailArr, function($message) use ($rEmail,$rName, $fairName,$cName){
			$message->to($rEmail, $rName)->subject($cName.' has Requested Interview at '.$fairName);
		});

		Mail::send('emails.ccInvitationEmail',$emailArr, function($message) use ($cEmail,$rName, $fairName,$cName){
			$message->to($cEmail, $cName)->subject('Interview requested to '.$rName.' at '.$fairName);
		});
 	}

 	public function cancelInterviewLink($u_id,$link){
		if ($link == 'c') {
			return env('BACKEND_URL').'cancel/interview/invitation/'.$u_id.'/mKShvcRE74yMwNczhOsQ';
		}
 		if ($link == 'r') {
 			return env('BACKEND_URL').'cancel/interview/invitation/'.$u_id.'/A2ZRbyKhtqoWbO2Pk5xt';
 		}
 	}

 	public function acceptInterviewLink($u_id,$link){
 		if ($link == 'c') {
			return env('BACKEND_URL').'interview/invitation/'.$u_id.'/mKShvcRE74yMwNczhOsQ';
 		}
 		if ($link == 'r') {
 			return env('BACKEND_URL').'interview/invitation/'.$u_id.'/A2ZRbyKhtqoWbO2Pk5xt';
 		}
 	}

 	public function getFormattedDate($date){
 		return Carbon::createFromFormat('d-m-Y',$date)->toFormattedDateString();
 	}




	public function inviteCandidate(Request $request)
	{
		$candidate_id = $request->candidate_id;
		$recruiter_id = $request->recruiter_id;
		$slot_id      = $request->slot_id;
		$fair_id      = $request->fair_id;
		$invited_by   = $request->invited_by;
		$candidate    = User::find($candidate_id);
		$recruiter    = User::find($recruiter_id);
		$fair         = Fair::find($fair_id);
		$recruiterSchedule = RecruiterSchedule::find($slot_id);
		// Check if Invited By Candidate
		if ($invited_by == 'candidate') {
			if (RecruiterScheduleInvite::where('fair_id',$fair_id)->where('recruiter_id',$recruiter_id)->where('candidate_id',$candidate_id)->where('slot_id',$slot_id)->where('status','pending')->exists()) {
				return response()->json([
		            'code'    => 'error',
		            'message' => 'You Are Already Invited By Recruiter On This Slot'
				],200);
			}
		}
		if ($recruiterSchedule) {
			if(!RecruiterScheduleBooked::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->where('start_time',$recruiterSchedule->start_time)->where('end_time',$recruiterSchedule->end_time)->where('date',$recruiterSchedule->days)->exists()){
				if($candidate){
					$name               = $candidate->name;
					$email              = $candidate->email;
					$recruiter_email    = $recruiter->email;
					$fairname           = $fair->name;
					$recruiter_name     = $recruiter->name;
					$candidate_timezone = $candidate->userSetting->user_timezone;
					$u_id = $this->getUniqueId();
					if (RecruiterScheduleInvite::where('fair_id',$fair_id)
						    ->where('recruiter_id',$recruiter_id)
						    ->where('candidate_id',$candidate_id)
						    ->exists())
				    {
						$SQL = RecruiterScheduleInvite::where('fair_id',$fair_id)
						        ->where('recruiter_id',$recruiter_id)
						        ->where('candidate_id',$candidate_id)
						        ->update([
						         	'expire'     => 0,
						         	'status'     => 'pending',
						         	'slot_id'    =>  $slot_id,
						         	'u_id'       =>  $u_id,
						         	'invited_by' =>  $invited_by
						        ]);
					}else{
						$SQL = RecruiterScheduleInvite::create(array(
							'u_id'         => $u_id,
							'fair_id'      => $fair_id,
							'recruiter_id' => $recruiter_id,
							'company_id'   => $recruiterSchedule->company_id,
							'candidate_id' => $candidate_id,
							'slot_id'      => $slot_id,
							'invited_by'   => $invited_by
						));
						RecruiterSchedule::where('id',$slot_id)->update(array('status' => 'pending'));
					}

					if($SQL){
						$rALink         = $this->acceptInterviewLink($u_id,'r');
						$cALink         = $this->acceptInterviewLink($u_id,'c');
						$rcancelLink    = $this->cancelInterviewLink($u_id,'r');
						$ccancelLink    = $this->cancelInterviewLink($u_id,'c');
						$backendLogin   = env('BACKEND_URL').'login';
						$date           = $this->getFormattedDate($recruiterSchedule->days);
				        $st             = $recruiterSchedule->start_time;
				        $et             = $recruiterSchedule->end_time;
				        $start_time     = $this->localTime($st,$candidate_timezone,$fair->timezone);
					    $end_time       = $this->localTime($et,$candidate_timezone,$fair->timezone);
					    $recruiter_date = $date;
					    $d1             = $this->localDateTime($date.$st,$candidate_timezone,$fair->timezone);
                    	$candidate_date = date("F jS, Y", strtotime($d1));

					    $emailArr = [
							'candidate_name'       => $name,
							'candidate_email'      => $email,
							'recruiter_name'       => $recruiter_name,
							'recruiter_email'      => $recruiter_email,
							'u_id'                 => $u_id,
							'candidate_id'         => $candidate_id,
							'fairname'             => $fairname,
							'recruiter_start_time' => $st,
							'recruiter_end_time'   => $et,
							'rALink'               => $rALink,
							'cALink'               => $cALink,
							'rcancelLink'          => $rcancelLink,
							'ccancelLink'          => $ccancelLink,
							'candidate_start_time' => $start_time,
							'candidate_end_time'   => $end_time,
							'recruiter_date'       => $recruiter_date,
							'candidate_date'       => $candidate_date
						];

					    if ($invited_by == 'recruiter') {
					    	// invitation from recruiter To candidate
					    	$this->rcInvitationEmail($emailArr);
					    }
					    if ($invited_by == 'candidate') {
					    	// invitation from candidate to recruiter
					    	$email = $recruiter_email;
					    	$this->crInvitationEmail($emailArr,$email,$name,$fairname,$recruiter_name);
					    }

						return response()->json([
				            'code'    => 'success',
				            'message' => 'Candidate Invited Successfully'
				        ],200);
					}else{
						return response()->json([
				            'code'    => 'success',
				            'message' => 'Candidate Not Invited Successfully'
				        ],200);
					}
				}
			}else{
                if ($invited_by == 'recruiter') {
					return response()->json([
			            'code'    => 'error',
			            'message' => 'Candidate is already engaged with other recruiter on this time slot '.$recruiterSchedule->days.' | '.$recruiterSchedule->start_time.'-'.$recruiterSchedule->end_time
			        ],200);
			    }
			    if ($invited_by == 'candidate') {
			    	$cTimeZone    = $this->candidateTimezone($candidate_id);
			    	$fTimezone = $this->fairTimezone($fair_id);
			    	return response()->json([
			            'code'    => 'error',
			            'message' => 'You are already engaged with other recruiter on this time slot '.$recruiterSchedule->days.' | '.$this->localTime($recruiterSchedule->start_time,$cTimeZone,$fTimezone).'-'.$this->localTime($recruiterSchedule->end_time,$cTimeZone,$fTimezone)
			        ],200);
			    }
			}
		}

	}

	public function localTime($time,$localTimezone,$fairTimezone)
	{
		$date = new DateTime($time, new DateTimeZone($fairTimezone));
		$date->setTimezone(new DateTimeZone($localTimezone));
		return $date->format('h:i A');
	}

	public function localDateTime($time,$localTimezone,$fairTimezone)
	{
		$date = new DateTime($time, new DateTimeZone($fairTimezone));
		$date->setTimezone(new DateTimeZone($localTimezone));
		return $date->format('Y-m-d h:i A');
	}

	public function currentLocalTime($time,$localTimezon)
	{
		$date = new DateTime($time, new DateTimeZone($localTimezon));
		$date->setTimezone(new DateTimeZone($localTimezon));
		return $date->format('Y-m-d h:i A');
	}

	public function getRecruiterAvailableSlots(Request $request){
		$slots = [];
		$fair_id            = $request->fair_id;
		$recruiter_id       = $request->recruiter_id;
		$date               = $request->date;
		$timezone           = $request->timezone;
		$schedules    = RecruiterSchedule::where('recruiter_id',$recruiter_id)->where('days',$date)->orderBy('days', 'ASC')->get();
		$fair         = Fair::find($fair_id);

		foreach ($schedules as $key => $row) {
			if(!RecruiterScheduleBooked::where('fair_id',$fair_id)->where('recruiter_id',$recruiter_id)->where('start_time',$row->start_time)->where('end_time',$row->end_time)->where('date',$row->days)->exists()){
				if (!RecruiterScheduleInvite::where('slot_id',$row->id)->where('status','pending')->exists()) {
					$date = new DateTime($row->days.$row->start_time,new DateTimeZone($fair->timezone));
					$date = $row->days.$row->start_time;
					$date = Carbon::createFromFormat('d-m-Y',$row->days);

					$currentDateTime = new DateTime("now",new DateTimeZone($fair->timezone));
                    $slotDateTime    = new DateTime($row->days.$row->start_time);

                    $slotStartDateTime = $row->days.$row->start_time;
                    $slotEndDateTime   = $row->days.$row->start_time;

                    $startTime = date('h:i A', strtotime($slotStartDateTime));
                    $end_time  = date('h:i A', strtotime($slotEndDateTime));

                    if (!empty($timezone)) {
                    	$isToolTip = '';
                    	$d1 = $this->localDateTime($slotStartDateTime,$timezone,$fair->timezone);
                    	$date = date("F jS, Y", strtotime($d1));
                    	if(date('Y-m-d', strtotime($slotStartDateTime)) != date('Y-m-d', strtotime($d1))){
                    		$isToolTip = 'yes';
                    	}
                    	$slots[] = [
							'id'        => $row->id,
							'slot'      => $this->localTime($row->start_time,$timezone,$fair->timezone).' - '.$this->localTime($row->end_time,$timezone,$fair->timezone),
							'date'      => $date,
							'isToolTip' => $isToolTip
						];
                    }else{
                    	$slots[] = [
							'id'   => $row->id,
							'slot' =>  $startTime.' - '.$end_time
						];
                    }
				}
		    }
		}

		return $slots;
	}

	public function getRecruiterAvailableSlotDates($fair_id,$recruiter_id,$candidate_id){
		$dates = [];
		$fair_id      = $fair_id;
		$recruiter_id = $recruiter_id;
		$candidate_id = $candidate_id;

		if (RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->whereIn('status',['pending','booked'])->exists()) {
			return response()->json([
	            'code'    => 'error',
	            'message' => 'You have already submitted interview request to this recruiter.'
	        ],200);
		}
		$schedules = RecruiterSchedule::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status','available')->select('id','days','start_time','end_time')->get();

		if (count($schedules) > 0) {
			foreach ($schedules as $key => $row) {
				$today = date('Y-m-d');
				$date  = date('Y-m-d',strtotime($row->days));
				if ($date >= $today) {
					$dates[] = [
						'id'   => $row->id,
						'date' => $date 
					];
				}
			}

			return response()->json([
	            'code'    => 'success',
	            'message' => 'Dates Found',
	            'data'    =>  $dates
	        ],200);

		}else{
			return response()->json([
	            'code'    => 'error',
	            'message' => 'No Slots Found'
	        ],200);
		}
	}




	public function invitationLink($u_id){
		$data = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		return $data;
	}

	public function candidateCancelInterview(Request $request){
		$slot_id = $request->slot_id;
		$u_id    = $request->u_id;
		$notes   = $request->notes;
		$slot    = RecruiterScheduleInvite::where('u_id',$u_id)->select('slot_id','candidate_id','recruiter_id')->first();
		RecruiterSchedule::where('id',$slot->slot_id)->update(array('status'=>'available'));
		RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'status'=>'canceled'));
		CandidateScheduleNote::create(array(
			'slot_id'      => $slot_id,
			'notes'        => $notes,
			'candidate_id' => $slot->candidate_id,
			'recruiter_id' => $slot->recruiter_id
        ));
		RecruiterScheduleBooked::where('u_id', $u_id)->delete();

		// if ($isDeleteSlot) {
			$this->generateCandidateCancelInterviewEmail($slot_id,$u_id,$notes,'candidate');
		// }

		return response()->json([
            'code'    => 'success',
            'message' => 'Interview Declined Successfully'
        ],200);
	}

	public function generateCandidateCancelInterviewEmail($slot_id,$u_id,$notes,$cancelBy){
		$slot = RecruiterScheduleInvite::where('slot_id',$slot_id)->first();
		$recruiterSchedule  = RecruiterSchedule::where('id',$slot_id)->first();
		$candidate          = User::find($slot->candidate_id);
		$recruiter          = User::find($slot->recruiter_id);
		$fair               = Fair::find($slot->fair_id);
		$candidate_timezone = $this->candidateTimezone($slot->candidate_id);
		$fair_timezone      = $this->fairTimezone($slot->fair_id);
		$fairCandidate      = FairCandidates::where('fair_id',$slot->fair_id)->where('candidate_id',$slot->candidate_id)->first();
		$faircandidate_id  = $fairCandidate->id;
		$candidate_id      = $candidate->id;
		$fairname          = $fair->name;

		if ($cancelBy == 'candidate') {
			$name              = $recruiter->name;
			$email             = $recruiter->email;
			$candidateName     = $candidate->name;
		}

		if ($cancelBy == 'recruiter') {
			$name              = $candidate->name;
			$email             = $candidate->email;
			$recruiterName     = $recruiter->name;
		}
		// $acceptInvitationLink = env('BACKEND_URL').'interview/invitation/'.$u_id;
		// $cancelUrl    = env('BACKEND_URL').'cancel/interview/invitation/'.$u_id;
		// $backendLogin = env('BACKEND_URL').'login';
		$slotDate   = $recruiterSchedule->days.$recruiterSchedule->start_time;
		$d1         = $this->localDateTime($slotDate,$candidate_timezone,$fair_timezone);
        $date       = date("F jS, Y", strtotime($d1));
        $start_time = date('h:i A', strtotime($recruiterSchedule->start_time));
        $end_time   = date('h:i A', strtotime($recruiterSchedule->end_time));

        if ($cancelBy == 'candidate') {
        	Mail::send('emails.candidateCancelInterview',
			[
				'name'             => $name,
				'email'            => $email,
				'candidateName'    => $candidateName,
				'fairname'         => $fairname,
				'faircandidate_id' => $faircandidate_id,
				'candidate_id'     => $candidate_id,
				'start_time'       => $start_time,
				'end_time'         => $end_time,
				'notes'            => $notes,
				'date'             => $this->getFormattedDate($recruiterSchedule->days),
				'cancelBy'         => $cancelBy

			], function($message) use ($email,$name, $fairname,$candidateName)
			{
				$message->to($email, $name)->subject($candidateName.' Declined Interview Request at '.$fairname);
			});
        }

        if ($cancelBy == 'recruiter') {
        	Mail::send('emails.candidateCancelInterview',
				[
					'name'  => $name,
					'email' => $email,
					'recruiterName' => $recruiterName,
					'fairname'      => $fairname,
					'faircandidate_id'  => $faircandidate_id,
					'candidate_id'      => $candidate_id,
					'start_time'        => $this->localTime($recruiterSchedule->start_time,$candidate_timezone,$fair_timezone),
					'end_time'          => $this->localTime($recruiterSchedule->end_time,$candidate_timezone,$fair_timezone),
					'date'              => $date,
					'cancelBy'          => $cancelBy

				], function($message) use ($email,$name, $fairname,$recruiterName)
			{
				$message->to($email, $name)->subject($recruiterName.' Declined Interview Request at '.$fairname);
			});
        }


		return true;
	}

	public function recruiterCancelInterview(Request $request){
		$slot_id = $request->slot_id;
		$u_id    = $request->u_id;
		$notes   = $request->notes;
		$slot    = RecruiterScheduleInvite::where('u_id',$u_id)->select('slot_id','candidate_id','recruiter_id')->first();
		RecruiterSchedule::where('id',$slot->slot_id)->update(array('status'=>'available'));
		RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'status'=>'canceled'));
		RecruiterScheduleBooked::where('u_id', $u_id)->delete();
		CandidateScheduleNote::create(array(
			'slot_id'      => $slot_id,
			'notes'        => $notes,
			'candidate_id' => $slot->candidate_id,
			'recruiter_id' => $slot->recruiter_id,
			'cancel_by'    => 1
        ));
		$this->generateCandidateCancelInterviewEmail($slot_id,$u_id,$notes,'recruiter');
		return response()->json([
            'code'    => 'success',
            'message' => 'Interview Canceled Successfully'
        ],200);
	}

	public function fetchSchedules(Request $req){
		$response = [];
		$date = $req->date;
		$u_id = $req->u_id;
		$bookedStart = array();
		$bookedEnd = array();
		$data = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		$schedules = RecruiterSchedule::where('recruiter_id',$data->recruiter_id)->where('days_arr', 'like', "%\"{$date}\"%")->get();

		$bookedSchedules = RecruiterScheduleBooked::where('date',$date)->where('fair_id',$data->fair_id)->where('recruiter_id',$data->recruiter_id)->where('is_approved',0)->get();
		foreach ($bookedSchedules as $key => $schedule) {
			$bookedStart[] = $schedule->start_time;
			$bookedEnd[] = $schedule->end_time;
		}

		$response['schedules'] = $schedules;
		$response['bookedStart'] = $bookedStart;
		$response['bookedEnd'] = $bookedEnd;

		return $response;
	}

	public function generateAddToCalendarLink($startDateTime,$endDateTime,$fairname){
		$dateFrom = date('Y-m-d H:i', strtotime($startDateTime));
		$dateTo   = date('Y-m-d H:i', strtotime($endDateTime));
		$from     = DateTime::createFromFormat('Y-m-d H:i',$dateFrom);
		$to       = DateTime::createFromFormat('Y-m-d H:i',$dateTo);

		$link = Link::create('Interview at '.$fairname, $from, $to)
		    ->description('Interview has successfully scheduled')
		    ->address('Fair Address');

		return $link->google();
	}

	public function bookSchedule($u_id,$confirm_by = ''){
		if (!empty($confirm_by) && $confirm_by == 'recruiter' || $confirm_by == 'candidate') {
			$fCondition = RecruiterScheduleInvite::where('u_id',$u_id)->exists();
			$dCondition = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		}else{
			$fCondition = RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->exists();
		    $dCondition = RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->first();
		}
		if (RecruiterScheduleInvite::where('u_id',$u_id)->where('status','canceled')->exists()) {

			return response()->json([
	            'code'    => 'error',
	            'message' => 'Interview Already Declined'
	        ],200);
		}
		if($fCondition){
			$data = $dCondition;
			$slot = RecruiterSchedule::find($data->slot_id);

			if(!RecruiterScheduleBooked::where('start_time',$slot->start_time)->where('end_time',$slot->end_time)->where('date',$slot->date)->exists()){

			// Set Zoom Meeting
			$fair  = Fair::find($slot->fair_id);
			$topic = "Interview Call";
		    $type  = 2;
		    $duration = 60;
		    $timzone  = $fair->timezone;
			$password = rand();
		    $meeting  = $this->setZoomMeeting($topic, $slot->start_time, $duration, $timzone, $password);
			$meeting  = json_decode($meeting, true);
			$booked   = RecruiterScheduleBooked::create(array(
				'u_id'         => $u_id,
				'fair_id'      => $data->fair_id,
				'candidate_id' => $data->candidate_id,
				'recruiter_id' => $data->recruiter_id,
				'start_time'   => $slot->start_time,
				'end_time'     => $slot->end_time,
				'date'         => $slot->days,
				'attended'       => 0,
				'meeting_id'	 => $meeting["id"],
				'host_id'	     => $meeting["host_id"],
				'start_url'		 => $meeting["start_url"],
				'join_url'		 => $meeting["join_url"],
				'password'		 => $password
			));
				if($booked){
					RecruiterSchedule::where('id',$data->slot_id)->update(['status'=>'booked']);
					RecruiterScheduleInvite::where('u_id',$u_id)->update(['expire' => 1,'status'=>'booked']);
					$candidate = User::find($data->candidate_id);
					$recruiter = User::find($data->recruiter_id);
					$fair = Fair::find($data->fair_id);
					$fairCandidate = FairCandidates::where('fair_id',$data->fair_id)->where('candidate_id',$data->candidate_id)->first();
					$faircandidate_id   = $fairCandidate->id;
					$candidate_id       = $candidate->id;
					$name               = $candidate->name;
					$email              = $candidate->email;
					$candidate_timezone = $candidate->userSetting->user_timezone;
					$fair_timezone      = $fair->timezone;
					$url                = $fair->shortname;
					$fairname           = $fair->name;
					$timezone           = $fair->timezone;
					$start_time         = $slot->start_time;
					$end_time           = $slot->end_time;
					$slotDate           = $slot->days;
					$recruiterName      = $recruiter->name;
					$recruiterEmail     = $recruiter->email;
					$cancelUrl          = $this->cancelInterviewLink($u_id,'c');
					$meetingLink        = env('FRONT_URL').$fair->short_name.'/recruiter/interview/room/'.$u_id;
					$d1                 = $this->localDateTime($slotDate.$start_time,$candidate_timezone,$fair_timezone);
                    $date               = date("F jS, Y", strtotime($d1));
					// $calendar_time      = date('Ymd', strtotime($slot->days))."T".date('His', strtotime($start_time))."/".date('Ymd', strtotime($slot->days))."T".date('His', strtotime($end_time));
				    $calendar_time = $this->generateAddToCalendarLink($date.$this->localTime($start_time,$candidate_timezone,$fair_timezone),$date.$this->localTime($end_time,$candidate_timezone,$fair_timezone),$fairname);
                    //Email For Candidate
					Mail::send('emails.interview-confirm',
						[
							'name'             => $name,
							'email'            => $email,
							'faircandidate_id' => $faircandidate_id,
							'candidate_id'     => $candidate_id,
							'url'              => $url,
							'fairname'         => $fairname,
							'start_time'       => $this->localTime($start_time,$candidate_timezone,$fair_timezone),
							'end_time'         => $this->localTime($end_time,$candidate_timezone,$fair_timezone),
							'date'             => $date,
							'u_id'             => $u_id,
							'timezone'         => $timezone,
							'calendar_time'    => $calendar_time,
							'recruiterName'    => $recruiterName,
							'withName'         => $recruiterName,
							'cancelUrl'        => $this->cancelInterviewLink($u_id,'c'),
							'meetingLink'      => $meetingLink
						], function($message) use ($email,$name,$fairname)
					{
					    $message->to($email, $name)->subject('Interview Successfully Scheduled at '.$fairname);
					});

					$calendar_time = $this->generateAddToCalendarLink($slotDate.$start_time,$slotDate.$end_time,$fairname);
					$date          = $this->getFormattedDate($slotDate);

					// Email For Recuiter
					Mail::send('emails.recruiter-interview-confirm',
						[
							'name'             => $recruiterName,
							'email'            => $recruiterEmail,
							'faircandidate_id' => $faircandidate_id,
							'candidate_id'     => $candidate_id,
							'url'              => $url,
							'fairname'         => $fairname,
							'start_time'       => $start_time,
							'end_time'         => $end_time,
							'date'             => $date,
							'u_id'             => $u_id,
							'timezone'         => $timezone,
							'calendar_time'    => $calendar_time,
							'candidateName'    => $candidate->name,
							'withName'         => $candidate->name,
							'cancelUrl'        => $this->cancelInterviewLink($u_id,'r'),
						], function($message) use ($recruiterEmail,$recruiterName,$fairname)
					{
					    $message->to($recruiterEmail, $recruiterName)->subject('Interview Successfully Scheduled at '.$fairname);
					});


					return response()->json([
			            'code'    => 'success',
			            'message' => 'Interview Booked Successfully'
			        ],200);
				}else{
					return response()->json([
			            'code'    => 'error',
			            'message' => 'Error'
			        ],200);
				}
			}else{
				return response()->json([
		            'code'    => 'taken',
		            'message' => 'Already Taken'
		        ],200);
			}
		}else{
			return response()->json([
	            'code'    => 'expire',
	            'message' => 'Link Expire'
	        ],200);
		}
	}
	/**
	 * Candidate Section.
	 *
	 */
	public function candidateFetchCalender(Request $req){
		$recruiter_id = $req->recruiter_id;
		$schedules = RecruiterScheduling::where('recruiter_id', '=', $recruiter_id)->get();
		$fair = Fair::find($req->fair_id);
		$data = array();
		$days = array();
		foreach($schedules as $schedule){
			$selectedDays = explode(" - ", $schedule->days);
			$date_from = strtotime($selectedDays[0]); // Convert date to a UNIX timestamp
			$date_to = strtotime($selectedDays[1]); // Convert date to a UNIX timestamp
			for ($i = $date_from; $i <= $date_to; $i+=86400) {
			    $days[] = date("j/n/Y", $i);
			}
		}

		$view = view('scheduling.calender',compact('days', 'data', 'recruiter_id', 'fair'))->render();
		return response()->json(['html'=>$view]);
	}
	public function candidateFetchSchedules(Request $req){
		$timestamp = strtotime($req->date);
		$date = date('d-m-Y', $timestamp);
		$recruiter_id = $req->recruiter_id;
		$bookedStart = array();
		$bookedEnd = array();
		$day = date('l', $timestamp);
		$schedules = RecruiterScheduling::where('recruiter_id', '=', $recruiter_id)->where('days_arr', 'like', "%\"{$date}\"%")->get();

		$bookedSchedules = RecruiterScheduleBooked::where('date', '=', $date)->where('fair_id', '=', Session::get('fair_id'))->where('recruiter_id', '=', $recruiter_id)->get();
		foreach ($bookedSchedules as $key => $schedule) {
			$bookedStart[] = $schedule->start_time;
			$bookedEnd[] = $schedule->end_time;
		}
		$row = 0;
		$d = date('l jS \of F Y', $timestamp);
		if ($req->ajax()) {
			$view = view('scheduling.candidate-timeslots',compact('schedules', 'd', 'bookedStart', 'bookedEnd', 'row', 'recruiter_id', 'date'))->render();
			return response()->json(['html'=>$view]);
		}
	}
	public function candidateBookSchedules(Request $req){
		$date         = $req->date;
		$recruiter_id = $req->recruiter;
		$start_time   = $req->start_time;
		$end_time     = $req->end_time;
		$candidate_id = $req->candidate_id;
		$fair_id      = $req->fair_id;
		$u_id         = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8).time().substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);


			if(!RecruiterScheduleBooked::where('start_time',$start_time)->where('end_time',$end_time)->where('date',$date)->where('recruiter_id',$recruiter_id)->exists()){
				$booked = RecruiterScheduleBooked::create(array(
					'u_id'         => $u_id,
					'fair_id'      => $fair_id,
					'candidate_id' => $candidate_id,
					'recruiter_id' => $recruiter_id,
					'start_time'   => $start_time,
					'end_time'     => $end_time,
					'date'         => $date,
					'attended'     => 0

				));
				if($booked){
					$candidate = User::find($candidate_id);
					$fair = Fair::find($fair_id);
					$fairCandidate = FairCandidates::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->first();
					$faircandidate_id = $fairCandidate->id;
					$candidate_id     = $candidate->id;
					$name             = $candidate->name;
					$email            = $candidate->email;
					$url              = $fair->short_name;
					$fairname      = $fair->name;
					$timezone      = $fair->timezone;
					$start_time    = $request->start_time;
		            $end_time      = $request->end_time;
					$calendar_time = date('Ymd', strtotime($date))."T".date('His', strtotime($start_time))."/".date('Ymd', strtotime($date))."T".date('His', strtotime($end_time));

					Mail::send('emails.interview-confirm', ['name' => $name, 'email' => $email, 'faircandidate_id' => $faircandidate_id, 'candidate_id' => $candidate_id, 'url' => $url, 'fairname' => $fairname, 'u_id' => $u_id, 'start_time' => $start_time, 'end_time' => $end_time, 'date' => $date, 'timezone' => $timezone, 'calendar_time' => $calendar_time], function($message) use ($email,$name)
					{
							$message->to($email, $name)->subject('Congratulation! Your interview is successfully scheduled');
					});
					return "Booked";
				}else{
					return "Error";
				}
			}else{
				return "Already Booked";
			}
	}
	public function candidateCancelSchedule($u_id){
		$schedule = RecruiterScheduleBooked::where('u_id', $u_id)->delete();
	    return view('scheduling.delete-confirmation');
	}

	public function localStatTime($timezone,$dt,$u_id){
		date_default_timezone_set($timezone);
		$data = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		$fair_timezone = $data->FairDetails->timezone;
    	$datetime = new DateTime($dt);
    	$datetime->format('d-m-Y H:i:s');
		$la_time  = new DateTimeZone($fair_timezone);
		$datetime->setTimezone($la_time);
		return $datetime->format('h:i A');
	}

	public function candidateFrontInterview(Request $request){
		$data = [];
		$candidate_id  = $request->candidate_id;
		$u_id  = $request->u_id;

		if (RecruiterScheduleBooked::where('u_id', $u_id)->exists()) {
			$schedule            = RecruiterScheduleBooked::where('u_id', $u_id)->first();
			if ($schedule->candidate_id == $candidate_id) {
				$schedule_date       = $schedule->date;
				$start_time          = $schedule->start_time;
				$end_time            = $schedule->end_time;

				$candidate_timezone  = $schedule->userSetting->user_timezone;
				$fairTimezone        = $this->fairTimezone($schedule->fair_id);
				$startDateAndTime    = $schedule_date.$start_time;
				$endDateAndTime       = $schedule_date.$end_time;
                $mST = $this->localDateTime($startDateAndTime,$candidate_timezone,$fairTimezone);
                $mET = $this->localDateTime($endDateAndTime,$candidate_timezone,$fairTimezone);
                $cT  = $this->currentLocalTime($request->dateTime,$candidate_timezone);

                // return $cT; die;

                // return [$cT,$mST,$mET];

                $meetingST = strtotime($mST);
                $mEndT     = strtotime($mET);
                $currentT  = strtotime($cT);



                if ($currentT < $meetingST) {
     //            	$time1 = new DateTime(date('Y-m-d H:i', strtotime($cT)));
	    //             $time2 = new DateTime(date('Y-m-d H:i', strtotime($mST)));
					// $timediff = $time1->diff($time2);
					// $dateToSend = [
					// 	'days'   => $timediff->format('%d'),
					// 	'hour'   => $timediff->format('%h'),
					// 	'minute' => $timediff->format('%i'),
					// 	'second' => $timediff->format('%s')
					// ];
					return response()->json([
			           'error'   => true,
			           'code'    => 'timeLeft',
			           'data'    => $mST,
			           'message' => 'Meeting Not Started Yet'
			        ], 200);
                }

                

                if ($currentT >= $meetingST && $currentT < $mEndT) {

    			    $recruiter      = User::find($schedule->recruiter_id);
    			    $data['fair']   = Fair::find($schedule->fair_id);
    	   			$recruiterInfo  = UserSettings::where('user_id',$schedule->recruiter_id)->first();
    	   			$recruiterInfo  = [
    	   				'id'         => $recruiter->id,
    	   				'company_id' => $recruiterInfo->company_id,
    	   				'fair_id'    => $recruiterInfo->fair_id,
    	   				'name'       => $recruiter->name,
    	   				'company_name'     => $recruiterInfo->companyDetail->company_name,
    	   				'title'            => $recruiterInfo->user_title,
    	   				'public_email'     => $recruiterInfo->public_email,
    	   				'linkedin'         => $recruiterInfo->linkedin_profile_link,
    	   				'recruiter_img'    => $recruiterInfo->recruiter_img,
    	   				'recruiter_status' => $recruiterInfo->recruiter_status,
    	   				'user_image'       => $recruiterInfo->user_image,
    	   				'location'         => $recruiterInfo->location,
    	   			];
    	   			$data['recruiterData'] = $recruiterInfo;
    	   			$data['slot']          = $schedule;
    	   			return $data;
                }else{
                	return response()->json([
			           'error'   => true,
			           'code'    => 'meetingEnd',
			           'message' => 'Meeting Timeout.'
			        ], 200);
                }

			}else{
				return response()->json([
		           'error'   => true,
		           'message' => 'Interview Schedule Not Found'
		        ], 200);
			}
			

		    // if ($userCurrentDate == $schedule_date) {
		    // 	if ($userCurrentTime > $start_time && $userCurrentTime < $end_time){
		    // 		$recruiter      = User::find($schedule->recruiter_id);
	     //   			$recruiterInfo  = UserSettings::where('user_id',$schedule->recruiter_id)->first();
	     //   			$recruiterInfo = [
	     //   				'id'         => $recruiter->id,
	     //   				'company_id' => $recruiterInfo->company_id,
	     //   				'fair_id'    => $recruiterInfo->fair_id,
	     //   				'name'       => $recruiter->name,
	     //   				'company_name'     => $recruiterInfo->companyDetail->company_name,
	     //   				'title'            => $recruiterInfo->user_title,
	     //   				'public_email'     => $recruiterInfo->public_email,
	     //   				'linkedin'         => $recruiterInfo->linkedin_profile_link,
	     //   				'recruiter_img'    => $recruiterInfo->recruiter_img,
	     //   				'recruiter_status' => $recruiterInfo->recruiter_status,
	     //   				'user_image'       => $recruiterInfo->user_image,
	     //   				'location'         => $recruiterInfo->location,
	     //   			];
	     //   			$data['recruiterData'] = $recruiterInfo;
	     //   			$data['slot']          = $schedule;

	     //   			return $data;

		    // 	  // return ['userCurrentDateTime'=>$userCurrentDate,'schedule_date'=>$schedule_date,'start_time'=>$start_time,'end_time'=>$end_time,'userCurrentTime'=>$userCurrentTime];
		    // 	}else{
    		// 		return response()->json([
    		//            'error'   => true,
    		//            'message' => 'Interview Date Is Not Available'
    		//         ], 200);
		    // 	}

		    	// $recruiter      = User::find($schedule->recruiter_id);
      	// 		$recruiterInfo  = UserSettings::where('user_id',$schedule->recruiter_id)->first();
      	// 		$recruiterInfo = [
      	// 			'id'         => $recruiter->id,
      	// 			'company_id' => $recruiterInfo->company_id,
      	// 			'fair_id'    => $recruiterInfo->fair_id,
      	// 			'name'       => $recruiter->name,
      	// 			'company_name'     => $recruiterInfo->companyDetail->company_name,
      	// 			'title'            => $recruiterInfo->user_title,
      	// 			'public_email'     => $recruiterInfo->public_email,
      	// 			'linkedin'         => $recruiterInfo->linkedin_profile_link,
      	// 			'recruiter_img'    => $recruiterInfo->recruiter_img,
      	// 			'recruiter_status' => $recruiterInfo->recruiter_status,
      	// 			'user_image'       => $recruiterInfo->user_image,
      	// 			'location'         => $recruiterInfo->location,
      	// 		];
      	// 		$data['recruiterData'] = $recruiterInfo;
      	// 		$data['slot']          = $schedule;
		    // }


		    // if ($date1 > $date2 && $date1 < $date3)
		    // {
		    //     $recruiter      = User::find($schedule->recruiter_id);
      //  			$recruiterInfo  = UserSettings::where('user_id',$schedule->recruiter_id)->first();
      //  			$recruiterInfo = [
      //  				'id'         => $recruiter->id,
      //  				'company_id' => $recruiterInfo->company_id,
      //  				'fair_id'    => $recruiterInfo->fair_id,
      //  				'name'       => $recruiter->name,
      //  				'company_name'     => $recruiterInfo->companyDetail->company_name,
      //  				'title'            => $recruiterInfo->user_title,
      //  				'public_email'     => $recruiterInfo->public_email,
      //  				'linkedin'         => $recruiterInfo->linkedin_profile_link,
      //  				'recruiter_img'    => $recruiterInfo->recruiter_img,
      //  				'recruiter_status' => $recruiterInfo->recruiter_status,
      //  				'user_image'       => $recruiterInfo->user_image,
      //  				'location'         => $recruiterInfo->location,
      //  			];
      //  			$data['recruiterData'] = $recruiterInfo;
      //  			$data['slot']          = $schedule;
		    // }else{
		    	// return ['date1'=>$date1,'date2'=>$date2,'date3'=>$date3];
		    // }
		// }else{
		// 		return response()->json([
		//            'error'   => true,
		//            'message' => 'Interview Date Is Not Available'
		//         ], 200);
		// }
	}else{
		return response()->json([
           'error'   => true,
           'message' => 'Interview Schedule Not Found'
        ], 200);
	}

  }
}
