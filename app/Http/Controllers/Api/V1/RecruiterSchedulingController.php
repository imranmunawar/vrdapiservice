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
use App\UserSettings;
use App\FairCandidates;
use App\RecruiterSchedule;
use App\RecruiterScheduleInvite;
use App\RecruiterScheduleBooked;
use App\CandidateScheduleNote;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Traits\ZoomMeetings;
use Spatie\CalendarLinks\Link;


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

		$schedules = RecruiterSchedule::where('fair_id',$req->fair_id)
		    ->where('company_id',$req->company_id)
		    ->where('recruiter_id',$req->recruiter_id)
		    ->orderBy("days")
		    ->get();

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
			      'slotStatus' => $this->slotStatus($row->id,$row->fair_id,$row->recruiter_id)
				];
			}
		}

		return $dataArr;
	}

	public function slotStatus($slot_id,$fair_id,$recruiter_id){
		$slot = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('slot_id',$slot_id)->select('cancel')->first();
		if ($slot) {
			if ($slot->cancel == 0) {
                return 'invited';
            }
            if ($slot->cancel == 1) {
                return 'canceled';
            }

            if ($slot->cancel == 2) {
                return 'booked';
            }
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


	public function getCandidateNotes($candidate_id){
		$notes = [];
		$candidateNotes = CandidateScheduleNote::where('candidate_id',$candidate_id)->get();
		if ($candidateNotes) {
			foreach ($candidateNotes as $key => $row) {
				// $date = Carbon::createFromFormat('d-m-Y',$row->created_at);
				// $date = Carbon::create($row->created_at);
				$date = Carbon::parse($row->created_at)->format('F j, Y g:i:s a');
				// $date = $date->englishDayOfWeek.', '.$date->toFormattedDateString();
				$notes[] = [
					'id'      => $row->id,
					'slot_id' => $row->slot_id,
					'date'    => $date,
					'note'    => $row->notes
				];
			}
		}

		return $notes;
	}

	public function interviewInvitations(Request $req){
		$interview_arr = array();
		$fair_id       = $req->fair_id;
		$recruiter_id  = $req->recruiter_id;
		$company_id    = $req->company_id;
		$start_date    = $req->start_date;
		$end_date      = $req->end_date;
		$slotStatus    = $req->status;

		if ($slotStatus != 'all') {
			$interviews = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('cancel',$slotStatus)->orderBy('created_at', 'DESC')->get();
		}else{
          $interviews = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->orderBy('created_at', 'DESC')->get();
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
			$date = Carbon::createFromFormat('d-m-Y',$interview['SlotInfo']['days']);
			$date = $date->englishDayOfWeek.', '.$date->toFormattedDateString().' '.date('h:i A', strtotime($interview->SlotInfo->start_time)).' - '.date('h:i A', strtotime($interview->SlotInfo->end_time));
			$interview_arr[] = array(
				'id'        => $interview->id,
				"u_id"      => $interview->u_id,
				'slot_id'   => $interview->slot_id,
				'notes'     => $this->getCandidateNotes($interview->candidate_id),
				'status'    => $interview->cancel,
				"name"      => $interview->CandidateDetails->name,
				'email'     => $interview->CandidateDetails->email,
				'slot'      => $date,
				'candidate_id' => $interview->candidate_id,
	    		"url"       => env('BACKEND_URL').'fair/candidate/detail/'.$interview->candidate_id
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
				$create = RecruiterSchedule::create(array(
					'fair_id'      => $request->fair_id,
					'company_id'   => $request->company_id,
					'recruiter_id' => $request->recruiter_id,
					'start_time'   => $start_time_interval,
					'end_time'     => $end_time_interval,
					'days'         => $day,
					'days_arr'     => json_encode($request->days),
					'available'    => '1'
				));
			}
		}

		// if ($create) {
		// 	return response()->json([
  //           'success' => true,
  //           'message' => 'Time Slots Added Successfully'
  //           ],200);
		// }

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




	public function inviteCandidate(Request $request)
	{
		$candidate_id = $request->candidate_id;
		$recruiter_id = $request->recruiter_id;
		$slot_id      = $request->slot_id;
		$fair_id      = $request->fair_id;
		$candidate    = User::find($candidate_id);
		$recruiter    = User::find($recruiter_id);
		$fair         = Fair::find($fair_id);
		$recruiterSchedule = RecruiterSchedule::find($slot_id);
		if ($recruiterSchedule) {
			if(!RecruiterScheduleBooked::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->where('start_time',$recruiterSchedule->start_time)->where('end_time',$recruiterSchedule->end_time)->where('date',$recruiterSchedule->days)->exists()){
				if($candidate){
					$name     = $candidate->name;
					$email    = $candidate->email;
					$fairname = $fair->name;
					$recruiter_name = $recruiter->name;
					$candidate_timezone = $candidate->userSetting->user_timezone;
					$u_id = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8).time().substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
					if (RecruiterScheduleInvite::where('fair_id',$fair_id)->where('recruiter_id',$recruiter_id)->where('candidate_id',$candidate_id)->exists()) {
						$SQL = RecruiterScheduleInvite::where('fair_id',$fair_id)
						            ->where('recruiter_id',$recruiter_id)
						            ->where('candidate_id',$candidate_id)
						            ->update(array('expire' => 0,'cancel'=> 0, 'slot_id'=>$slot_id,'u_id'=>$u_id));
					}else{
						$SQL = RecruiterScheduleInvite::create(array(
							'u_id'         => $u_id,
							'fair_id'      => $fair_id,
							'recruiter_id' => $recruiter_id,
							'candidate_id' => $candidate_id,
							'slot_id'      => $slot_id
						));
					}


					if($SQL){
						$emails = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->first();
						$faircandidate_id = $emails->id;
							$acceptInvitationLink = env('BACKEND_URL').'interview/invitation/'.$u_id;
						$cancelUrl    = env('BACKEND_URL').'cancel/interview/invitation/'.$u_id;
						$backendLogin = env('BACKEND_URL').'login';
						$date = Carbon::createFromFormat('d-m-Y',$recruiterSchedule->days)->toFormattedDateString();
				        $start_time = $recruiterSchedule->start_time;
				        $end_time   = $recruiterSchedule->end_time;
				        $start_time = AppHelper::startTimeScheduling($start_time, $u_id, $candidate_timezone)->format('h:i A');
					    $end_time = AppHelper::endTimeScheduling($end_time, $u_id, $candidate_timezone)->format('h:i A');

						// if($emails->email_notification == 1){
							Mail::send('emails.scheduling-invitation',
								[
									'name'  => $name,
									'email' => $email,
									'u_id'  => $u_id,
									'faircandidate_id' => $faircandidate_id,
									'candidate_id'     => $candidate_id,
									'fairname'         => $fairname,
									'acceptInvitationLink'=>$acceptInvitationLink,
									'cancelUrl'           => $cancelUrl,
									'start_time'          => $start_time,
									'end_time'            => $end_time,
									'date'                => $date

								], function($message) use ($email,$name, $fairname, $recruiter_name)
							{

								// $filename = "invite.ics";
								// 		$meeting_duration = (3600 * 2); // 2 hours
								// 		$meetingstamp = strtotime( '12-6-2020' . " UTC");
								// 		$dtstart = gmdate('Ymd\THis\Z', $meetingstamp);
								// 		$dtend =  gmdate('Ymd\THis\Z', $meetingstamp + $meeting_duration);
								// 		$todaystamp = gmdate('Ymd\THis\Z');
								// 		$uid = date('Ymd').'T'.date('His').'-'.rand().'@yourdomain.com';
								// 		$description = strip_tags('asdasdasdasdasdadasdasd');
								// 		$location = "651 Chester Rd, Ellesmere Port, CH66 2LN";
								// 		$titulo_invite = "Your meeting title";
								// 		$organizer = "CN=Ali Sultwn Khan:mailto:noreply@goalenvision.com";
										
								// 		// ICS
								// 		$mail[0]  = "BEGIN:VCALENDAR";
								// 		$mail[1] = "PRODID:-//Google Inc//Google Calendar 70.9054//EN";
								// 		$mail[2] = "VERSION:2.0";
								// 		$mail[3] = "CALSCALE:GREGORIAN";
								// 		$mail[4] = "METHOD:REQUEST";
								// 		$mail[5] = "BEGIN:VEVENT";
								// 		$mail[6] = "DTSTART;TZID=America/Sao_Paulo:" . $dtstart;
								// 		$mail[7] = "DTEND;TZID=America/Sao_Paulo:" . $dtend;
								// 		$mail[8] = "DTSTAMP;TZID=America/Sao_Paulo:" . $todaystamp;
								// 		$mail[9] = "UID:" . $uid;
								// 		// $mail[10]  = "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS";
								// 		// $mail[11]  = "ACTION;RSVP=TRUE;CN=Test:mailto:user123@gmail.com";
								// 		$mail[12] = "ORGANIZER;" . $organizer;
								// 		$mail[13] = "CREATED:" . $todaystamp;
								// 		$mail[14] = "DESCRIPTION:" . $description;
								// 		$mail[15] = "LAST-MODIFIED:" . $todaystamp;
								// 		$mail[16] = "LOCATION:" . $location;
								// 		$mail[17] = "SEQUENCE:0";
								// 		$mail[18] = "SUMMARY:" . $titulo_invite;
								// 		$mail[19] = "STATUS:CONFIRMED";
								// 		$mail[20] = "TRANSP:OPAQUE";
								// 		$mail[21] = "END:VEVENT";
								// 		$mail[22] = "END:VCALENDAR";
										
								// 		$mail = implode("\r\n", $mail);
								// 		header("text/calendar");
								// 		file_put_contents($filename, $mail);
                                        
								// 		$message->attachData($mail, $filename, ['mime' => 'text/calendar; method=REQUEST; charset=UTF-8']);
								$message->to($email, $name)->subject($recruiter_name.' invited you for the interview on '.$fairname);

							});
						// }
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
				return response()->json([
		            'code'    => 'error',
		            'message' => 'Candidate is already engaged with other recruiter on this time '.$recruiterSchedule->days.' | '.$recruiterSchedule->start_time.'-'.$recruiterSchedule->end_time
		        ],200);
			}
		}

	}

	public function getRecruiterAvailableSlots($fair_id,$recruiter_id,$date){
		$slots = [];
		$fair_id      = $fair_id;
		$recruiter_id = $recruiter_id;
		$date         = $date;
		$schedules    = RecruiterSchedule::where('recruiter_id',$recruiter_id)->where('days',$date)->orderBy('days', 'ASC')->get();
		$fair         = Fair::find($fair_id);

		foreach ($schedules as $key => $row) {
			if(!RecruiterScheduleBooked::where('fair_id',$fair_id)->where('recruiter_id',$recruiter_id)->where('start_time',$row->start_time)->where('end_time',$row->end_time)->where('date',$row->days)->exists()){
				if (!RecruiterScheduleInvite::where('slot_id',$row->id)->where('cancel',0)->exists()) {
					$date = new DateTime($row->days.$row->start_time,new DateTimeZone($fair->timezone));
					$date = $row->days.$row->start_time;
					$date = Carbon::createFromFormat('d-m-Y',$row->days);
					// $start_time = Carbon::createFromFormat('H:i',$row->start_time)->format('H:i');
					// $end_time   = Carbon::createFromFormat($row->end_time);

					$currentDateTime = new DateTime("now",new DateTimeZone($fair->timezone));
                    $slotDateTime    = new DateTime($row->days.$row->start_time);

                    // if ($slotDateTime->format('d-m-Y H:i:s') > $currentDateTime->format('d-m-Y H:i:s')) {
                    	$slots[] = [
							'id'   => $row->id,
							'slot' => date('h:i A', strtotime($row->start_time)).' - '.date('h:i A', strtotime($row->end_time)),
						];
                    // }
				}
		    }
		}

		return $slots;
	}




	public function invitationLink($u_id){
		$data = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		return $data;
	}

	public function candidateCancelInterview(Request $request){
		$slot_id = $request->slot_id;
		$u_id    = $request->u_id;
		$notes   = $request->notes;
		RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'cancel'=>1));
		$invite = RecruiterScheduleInvite::where('u_id',$u_id)->first();
		CandidateScheduleNote::create(array(
			'slot_id'      => $slot_id,
			'notes'        => $notes,
			'candidate_id' => $invite->candidate_id
        ));
		RecruiterScheduleBooked::where('u_id', $u_id)->delete();

		// if ($isDeleteSlot) {
			$this->generateCandidateCancelInterviewEmail($slot_id,$u_id,$notes,'candidate');
		// }

		return response()->json([
            'code'    => 'success',
            'message' => 'Interview Cancel Successfully'
        ],200);
	}

	public function generateCandidateCancelInterviewEmail($slot_id,$u_id,$notes,$cancelBy){
		$slot = RecruiterScheduleInvite::where('slot_id',$slot_id)->first();
		$recruiterSchedule = RecruiterSchedule::where('id',$slot_id)->first();
		$candidate         = User::find($slot->candidate_id);
		$recruiter         = User::find($slot->recruiter_id);
		$fair              = Fair::find($slot->fair_id);
		$fairCandidate     = FairCandidates::where('fair_id',$slot->fair_id)->where('candidate_id',$slot->candidate_id)->first();
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
		$date = Carbon::createFromFormat('d-m-Y',$recruiterSchedule->days)->toFormattedDateString();
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
					'date'             => $date,
					'cancelBy'         => $cancelBy

				], function($message) use ($email,$name, $fairname,$candidateName)
			{
				$message->to($email, $name)->subject($candidateName.' rejected the interview request in '.$fairname);
			});
        }

        if ($cancelBy == 'recruiter') {
        	Mail::send('emails.candidateCancelInterview',
				[
					'name'  => $name,
					'email' => $email,
					'recruiterName' => $recruiterName,
					'fairname'      => $fairname,
					'faircandidate_id' => $faircandidate_id,
					'candidate_id'   => $candidate_id,
					'start_time'    => $start_time,
					'end_time'      => $end_time,
					'date'          => $date,
					'cancelBy'         => $cancelBy

				], function($message) use ($email,$name, $fairname,$recruiterName)
			{
				$message->to($email, $name)->subject($recruiterName.' rejected the interview '.$fairname);
			});
        }


		return true;
	}

	public function recruiterCancelInterview(Request $request){
		$slot_id = $request->slot_id;
		$u_id    = $request->u_id;
		$notes   = '';
		RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'cancel'=>1));
		RecruiterScheduleBooked::where('u_id', $u_id)->delete();
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

	public function bookSchedule($u_id){
		$u_id    = $u_id;
		if(RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->exists()){
			$data = RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->first();
			$slot = RecruiterSchedule::find($data->slot_id);
			if(!RecruiterScheduleBooked::where('start_time',$slot->start_time)->where('end_time',$slot->end_time)->where('date',$slot->date)->exists()){
				// Set Zoom Meeting
				$fair = Fair::find($slot->fair_id);
				$topic = "Interview Call";
		    $type = 2;
		    $duration = 60;
		    $timzone = $fair->timezone;
				$password = rand();
		    $meeting = $this->setZoomMeeting($topic, $slot->start_time, $duration, $timzone, $password);
				$meeting = json_decode($meeting, true);
				$booked = RecruiterScheduleBooked::create(array(
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
					RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'cancel'=>2));
					$candidate = User::find($data->candidate_id);
					$recruiter = User::find($data->recruiter_id);
					$fair = Fair::find($data->fair_id);
					$fairCandidate = FairCandidates::where('fair_id',$data->fair_id)->where('candidate_id',$data->candidate_id)->first();
					$faircandidate_id   = $fairCandidate->id;
					$candidate_id       = $candidate->id;
					$name               = $candidate->name;
					$email              = $candidate->email;
					$candidate_timezone = $candidate->userSetting->user_timezone;
					$url                = $fair->shortname;
					$fairname           = $fair->name;
					$timezone           = $fair->timezone;
					$start_time         = $slot->start_time;
					$end_time           = $slot->end_time;
					$date               = $slot->days;
					$recruiterName      = $recruiter->name;
					$recruiterEmail     = $recruiter->email;
					$cancelUrl    = env('BACKEND_URL').'cancel/interview/invitation/'.$u_id;
					$meetingLink  = env('FRONT_URL').$fair->short_name.'/recruiter/interview/room/'.$u_id;
					$date         = Carbon::createFromFormat('d-m-Y',$date);
					$date         = $date->englishDayOfWeek.', '.$date->toFormattedDateString();
					$start_time = AppHelper::startTimeScheduling($start_time, $u_id, $candidate_timezone)->format('h:i A');
					$end_time   = AppHelper::endTimeScheduling($end_time, $u_id, $candidate_timezone)->format('h:i A');
					$calendar_time = date('Ymd', strtotime($slot->days))."T".date('His', strtotime($start_time))."/".date('Ymd', strtotime($slot->days))."T".date('His', strtotime($end_time));
                    //Email For Candidate
					Mail::send('emails.interview-confirm',
						[
							'name'  => $name,
							'email' => $email,
							'faircandidate_id' => $faircandidate_id,
							'candidate_id'     => $candidate_id,
							'url'        => $url,
							'fairname'   => $fairname,
							'start_time' => $start_time,
							'end_time'   => $end_time,
							'date'       => $date,
							'u_id'       => $u_id,
							'timezone'   => $timezone,
							'calendar_time' => $calendar_time,
							'recruiterName' => $recruiterName,
							'withName'      => $recruiterName,
							'cancelUrl'     => $cancelUrl,
							'meetingLink'   => $meetingLink
						], function($message) use ($email,$name)
					{
					    $message->to($email, $name)->subject('Congratulation! Your interview is successfully scheduled');
					});


					// Email For Recuiter
					Mail::send('emails.recruiter-interview-confirm',
						[
							'name'  => $recruiterName,
							'email' => $recruiterEmail,
							'faircandidate_id' => $faircandidate_id,
							'candidate_id'     => $candidate_id,
							'url'        => $url,
							'fairname'   => $fairname,
							'start_time' => $start_time,
							'end_time'   => $end_time,
							'date'       => $date,
							'u_id'       => $u_id,
							'timezone'   => $timezone,
							'calendar_time' => $calendar_time,
							'candidateName' => $candidate->name,
							'withName'      =>$candidate->name,
							'cancelUrl'     => $cancelUrl,
						], function($message) use ($recruiterEmail,$recruiterName)
					{
					    $message->to($recruiterEmail, $recruiterName)->subject('Congratulation! Interview is successfully scheduled');
					});


					return response()->json([
			            'code'    => 'success',
			            'message' => 'Booked'
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
		// $fair_short_name     = $request->fair_short_name;
		$u_id  = $request->u_id;
		$d  = date('d-m-Y H:i:s', strtotime($request->dateTime));
		$userCurrentDate  = date('d-m-Y', strtotime($request->dateTime));
		$userCurrentTime  = date('h:i A', strtotime($request->dateTime));
		// $schedule         = RecruiterScheduleBooked::where('u_id', $u_id)->exists();

		if (RecruiterScheduleBooked::where('u_id', $u_id)->exists()) {
			$schedule            = RecruiterScheduleBooked::where('u_id', $u_id)->first();
			if ($schedule->candidate_id == $candidate_id) {
				$schedule_date       = $schedule->date;
				$start_time          = $schedule->start_time;
				$end_time            = $schedule->end_time;
				$candidate_timezone  = $schedule->userSetting->user_timezone;
				$userCurrentDate     = AppHelper::dateScheduling($userCurrentDate, $u_id, $candidate_timezone)->format('d-m-Y');
				$userCurrentTime     = $this->localStatTime($candidate_timezone,$d,$u_id);
				$schedule_date       = AppHelper::dateScheduling($schedule_date, $u_id, $candidate_timezone)->format('d-m-Y');
				$start_time          = AppHelper::startTimeScheduling($start_time, $u_id, $candidate_timezone)->format('h:i A');
			    $end_time            = AppHelper::endTimeScheduling($end_time, $u_id, $candidate_timezone)->format('h:i A');


			    $recruiter      = User::find($schedule->recruiter_id);
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
