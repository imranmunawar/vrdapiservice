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
use App\FairCandidates;
use App\RecruiterSchedule;
use App\RecruiterScheduleInvite;
use App\RecruiterScheduleBooked;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class RecruiterSchedulingController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $req)
	{
		$dataArr = [];
		$schedules = RecruiterSchedule::where('fair_id',$req->fair_id)
		            ->where('company_id',$req->company_id)
		            ->where('recruiter_id',$req->recruiter_id)
		            ->get();
		if ($schedules) {
			foreach ($schedules as $key => $row) {
				$dataArr[] = [
				  'id'           => $row->id,
				  'fair_id'      => $row->fair_id,
			      'company_id'   => $row->company_id,
			      'recruiter_id' => $row->recruiter_id,
			      'recruiter_name' => $row->RecruiterDetails->name,
			      'candidate_id' => $row->candidate_id,
			      'start_time'   => $row->start_time,
			      'end_time'     => $row->end_time,
			      'days'         => $row->days,
			      'days_arr'     => $row->days_arr,
			      'available'    => $row->available
				];
			}
		}

		return $dataArr;

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
				$interview_arr[] = array(
					"id"   => $schedule->id,
					"name" => $schedule->start_time.' - '.$schedule->end_time.' <i class="fas fa-times-circle pull-right deleteSlotIcon" onclick="deleteSlot('.$schedule->id.')" title="asdasdasd"></i>',
		    		"startdate" => $day,
		    		"enddate"   => $day,
		    		"starttime" => $schedule->start_time,
		    		"endtime"   => $schedule->end_time,
		    		"color"     => "#37BC9B",
		    		"url"       => "",
		    		'title'     => 'asdadasdasd'
				);
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
	    		"url"       => env('BACKEND_URL').'fair/candidate/detail/'.$interview->candidate_id
			);
		}

		return $interview_arr;
	}

	public function interviewInvitations(Request $req){
		$interview_arr = array();
		$fair_id      = $req->fair_id;
		$recruiter_id = $req->recruiter_id;
		$company_id   = $req->company_id;

		$interviews = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->orderBy('created_at', 'ASC')->get();

		// $interview_arr = array();
		foreach ($interviews as $key => $interview) {
			$date = Carbon::createFromFormat('Y-m-d',$interview->SlotInfo->days);
			$date = $date->englishDayOfWeek.', '.$date->toFormattedDateString().' '.date('h:i A', strtotime($interview->SlotInfo->start_time)).' - '.date('h:i A', strtotime($interview->SlotInfo->end_time));
			$interview_arr[] = array(
				'id'        => $interview->id,
				"u_id"      => $interview->u_id,
				'notes'     => $interview->notes,
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
		//     $days[] = date("Y-m-d", $i);
		// }

		$selectedDays = explode(" - ", $request->days);
		$date_from = strtotime($selectedDays[0]); // Convert date to a UNIX timestamp
		$date_to = strtotime($selectedDays[1]); // Convert date to a UNIX timestamp
		$days = array();
		for ($i = $date_from; $i <= $date_to; $i+=86400) {
		    $days[] = date("Y-m-d", $i);
		}

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
					$start_time_interval = date('H:i', strtotime("+$intervel minutes", strtotime($start_time_interval)));
				}
				$end_time_interval = date('H:i', strtotime("+$intervel minutes", strtotime($start_time_interval)));
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
				$days[] = date("Y-m-d", $i);
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
 		$delete = RecruiterSchedule::where('id',$id)->delete();
		if ($delete) {
			return response()->json([ 
            'success' => true, 
            'message' => 'Time Slot Deleted Successfully' 
            ],200);
		}
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
			if(!RecruiterScheduleBooked::where('start_time',$recruiterSchedule->start_time)->where('end_time',$recruiterSchedule->end_time)->where('date',$recruiterSchedule->days)->exists()){
				if($candidate){
					$name     = $candidate->name;
					$email    = $candidate->email;
					$fairname = $fair->name;
					$recruiter_name = $recruiter->name;
					$u_id = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8).time().substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 8);
					$SQL = RecruiterScheduleInvite::create(array(
						'u_id'         => $u_id,
						'fair_id'      => $fair_id,
						'recruiter_id' => $recruiter_id,
						'candidate_id' => $candidate_id,
						'slot_id'      => $slot_id
					));

					if( $SQL){
						$emails = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->first();
						$faircandidate_id = $emails->id;
						$acceptInvitationLink = env('BACKEND_URL').'interview/invitation/'.$u_id;
						$cancelUrl    = env('BACKEND_URL').'cancel/interview/invitation/'.$u_id;
						$backendLogin = env('BACKEND_URL').'login';
						$date = Carbon::createFromFormat('Y-m-d',$recruiterSchedule->days)->toFormattedDateString();
				        $start_time = date('h:i A', strtotime($recruiterSchedule->start_time));
				        $end_time   = date('h:i A', strtotime($recruiterSchedule->end_time));
						if($emails->email_notification == 1){
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
								$message->to($email, $name)->subject($recruiter_name.' invited you for the interview on '.$fairname);
							});
						}
						return response()->json([  
				            'code'    => 'success',
				            'message' => 'Candidate Invited Successfully' 
				        ],200);
					}else{
						return response()->json([  
				            'code'    => 'error',
				            'message' => 'Candidate Not Invited Successfully' 
				        ],200);
					}
				}
			}
		}
		
	}

	public function getRecruiterAvailableSlots($fair_id,$recruiter_id){
		$slots = [];
		$fair_id      = $fair_id;
		$recruiter_id = $recruiter_id;
		$schedules    = RecruiterSchedule::where('recruiter_id',$recruiter_id)->orderBy('days', 'ASC')->get();
		$fair         = Fair::find($fair_id);

		foreach ($schedules as $key => $row) {
			if(!RecruiterScheduleBooked::where('fair_id',$fair_id)->where('recruiter_id',$recruiter_id)->where('start_time',$row->start_time)->where('end_time',$row->end_time)->where('date',$row->days)->exists()){
				if (!RecruiterScheduleInvite::where('slot_id',$row->id)->where('cancel',0)->exists()) {
					$date = new DateTime($row->days.$row->start_time,new DateTimeZone($fair->timezone));
					$date = $row->days.$row->start_time;
					$date = Carbon::createFromFormat('Y-m-d',$row->days);
					// $start_time = Carbon::createFromFormat('H:i',$row->start_time)->format('H:i');
					// $end_time   = Carbon::createFromFormat($row->end_time);

					$currentDateTime = new DateTime("now",new DateTimeZone($fair->timezone));
                    $slotDateTime    = new DateTime($row->days.$row->start_time);

                    if ($slotDateTime->format('Y-m-d H:i:s') > $currentDateTime->format('Y-m-d H:i:s')) {
                    	$slots[$date->englishDayOfWeek.', '.$date->toFormattedDateString()][] = [
							'id'   => $row->id,
							'slot' => date('h:i A', strtotime($row->start_time)).' - '.date('h:i A', strtotime($row->end_time)),
						];
                    }
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
		if(RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->exists()){
			RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'notes'=>$notes,'cancel'=>1));
			RecruiterScheduleBooked::where('u_id', $u_id)->delete();
			return response()->json([  
	            'code'    => 'success',
	            'message' => 'Interview Cancel Successfully' 
	        ],200);
		}
	}

	public function recruiterCancelInterview(Request $request){
		$slot_id = $request->slot_id;
		$u_id    = $request->u_id;
		if(RecruiterScheduleInvite::where('u_id',$u_id)->where('expire',0)->exists()){
			RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1,'cancel'=>1));
			RecruiterScheduleBooked::where('u_id', $u_id)->delete();
			return response()->json([  
	            'code'    => 'success',
	            'message' => 'Interview Cancel Successfully' 
	        ],200);
		}
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
				$booked = RecruiterScheduleBooked::create(array(
					'u_id'         => $u_id,
					'fair_id'      => $data->fair_id,
					'candidate_id' => $data->candidate_id,
					'recruiter_id' => $data->recruiter_id,
					'start_time'   => $slot->start_time,
					'end_time'     => $slot->end_time,
					'date'         => $slot->days,
					'attended'     => 0
				));
				if($booked){
					RecruiterScheduleInvite::where('u_id',$u_id)->update(array('expire' => 1));
					$candidate = User::find($data->candidate_id);
					$recruiter = User::find($data->recruiter_id);
					$fair = Fair::find($data->fair_id);
					$fairCandidate = FairCandidates::where('fair_id',$data->fair_id)->where('candidate_id',$data->candidate_id)->first();
					$faircandidate_id = $fairCandidate->id;
					$candidate_id     = $candidate->id;
					$name  = $candidate->name;
					$email = $candidate->email;
					$url   = $fair->shortname;
					$fairname      = $fair->name;
					$timezone      = $fair->timezone;
					$start_time    = $slot->start_time;
					$end_time      = $slot->end_time;
					$date          = $slot->days;
					$recruiterName = $recruiter->name;
					$recruiterEmail = $recruiter->email;
					$cancelUrl    = env('BACKEND_URL').'cancel/interview/invitation/'.$u_id;
					$meetingLink  = env('BACKEND_URL').'recruiter/meeting/room/'.$u_id;
					$date         = Carbon::createFromFormat('Y-m-d',$date);
					$date         = $date->englishDayOfWeek.', '.$date->toFormattedDateString();
					$start_time   = date('h:i A', strtotime($start_time));
					$end_time     = date('h:i A', strtotime($end_time));
					$calendar_time = date('Ymd', strtotime($slot->days))."T".date('His', strtotime($start_time))."/".date('Ymd', strtotime($slot->days))."T".date('His', strtotime($end_time));

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
							'cancelUrl'     => $cancelUrl
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
		$date = date('Y-m-d', $timestamp);
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
}
