<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Fair;
use App\FairHalls;
use App\Company;
use App\CompanyJob;
use App\FairSetting;
use App\CareerTest;
use App\CareerTestAnswer;
use App\JobQuestionnaire;
use App\RecruiterQuestionnaire;
use App\WebinarQuestionnaire;
use App\CandidateTest;
use App\CandidateTurnout;
use App\UserSettings;
use App\Traits\MatchingJobs;
use App\Traits\MatchingRecruiters;
use App\Traits\MatchingWebinars;
use App\Traits\TrackCandidates;
use App\Traits\FairLiveEmailNotification;
use App\Traits\FairEndEmailCandidates;
use App\FairCandidates;
use App\CandidateAgenda;
use App\CompanyWebinar;
use App\CometChatPro;
use \Input as Input;
use DB;
use Carbon\Carbon;

class FairController extends Controller
{
    use MatchingJobs, MatchingRecruiters, MatchingWebinars, TrackCandidates,FairLiveEmailNotification,FairEndEmailCandidates;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $fairsArr = [];
      $fairs = Fair::all();
      if ($fairs) {
        foreach ($fairs as $key => $fair) {
          $fairsArr[]=[
            'id'             => $fair['id'],
            'name'           => $fair['name'],
            'short_name'     => $fair['short_name'],
            'email'          => $fair['email'],
            'fair_image'     => $fair['fair_image'],
            'register_time'  => Carbon::createFromFormat('Y-m-d H:i:s',  $fair['register_time'])->format('F j, Y g:i A'),
            'start_time'     => Carbon::createFromFormat('Y-m-d H:i:s',  $fair['start_time'])->format('F j, Y g:i A'),
            'end_time'       => Carbon::createFromFormat('Y-m-d H:i:s',  $fair['end_time'])->format('F j, Y g:i A'),
            'fair_type'      => $fair['fair_type'],
            'organiser_name' => $fair['organizer']['name'],
            'organiser_id'   => $fair['organizer']['id'],
            'fair_status'    => $fair['fair_status'],
          ];
        }
      }
      return response()->json($fairsArr);
    }

    public function testRoute(){
        echo env('S3_PRIVATE_EP'); die;
    }
    
    public function aboutHall($fair_id)
    {
        $fair = Fair::find($fair_id);
        return response()->json(['total_hall'=>$fair->total_hall],200);
    }
    
    public function hallnames($fair_id)
    {
        $fair = FairHalls::where('fair_id',$fair_id)->get();
        return response()->json($fair);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Create a new Fair in the database...
        $fair = Fair::create($request->all());
        $insertId = $fair->id;
        if (!$fair) {
            return response()->json(['success' => false,'message' => 'Fair Not Created Successfully'],200);
        }
        return response()->json(['success' => true,'message' => 'Fair Created Successfully', 'insertId' => $insertId ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $fair = Fair::find($id);
        return response()->json($fair);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fairChatApiDetail($id)
    {
      $dataArr = [];
      $fair = Fair::find($id);
      $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();

      $dataArr = [
        'id'           => $fair->id,
        'organiser_id' => $fair->organiser_id,
        'name'         => $fair->name,
        'short_name'   => $fair->short_name,
        'fair_image'   => $fair->fair_image,
        'layout'       => $fair->layout,
        'app_id'       => $cometApiDetail->app_id,
        'api_key'      => $cometApiDetail->api_key,
        'rest_api_key'  => $cometApiDetail->rest_api_key,
        'region'       => $cometApiDetail->region
      ];
      return response()->json($dataArr);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function organizerChatApiDetail($organizerId)
    {
      $dataArr = [];
      $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
      if ($cometApiDetail) {
        $dataArr = [
          'app_id'       => $cometApiDetail->app_id,
          'api_key'      => $cometApiDetail->api_key,
          'rest_api_key'  => $cometApiDetail->rest_api_key,
          'region'       => $cometApiDetail->region
        ];
      }
      return $dataArr;
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $fair = Fair::find($id);
        return response()->json($fair);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();        
        $fair  = Fair::findOrFail($id);
        $fair->fill($data)->save();
        return response()->json([
           'success' => true,
           'message' => 'Fair Updated Successfully'
        ], 200);

    }
    
    public function createFairHall(Request $request)
    {
        $data = $request->all();
        if($data['total_hall'] > 1){
           FairHalls::where('fair_id',$id)->delete();
           for($i= 1; $i <= $data['total_hall']; $i++){
              $multi_hall = array(
                  'fair_id' => $id,
                  'hall_id' => $i,
                  'hall_name' => $data['hall_name_'.$i.''],
              );
              FairHalls::create($multi_hall);
           }
        }
        return response()->json([
           'success' => true,
           'message' => 'Fair Hall Updated Successfully'
        ], 200);

    }
    
    public function updateFairHall(Request $request, $id)
    {
        $data = $request->all();
        if($data['total_hall'] > 1){
           FairHalls::where('fair_id',$id)->delete();
           for($i= 1; $i <= $data['total_hall']; $i++){
              $multi_hall = array(
                  'fair_id' => $id,
                  'hall_id' => $i,
                  'hall_name' => $data['hall_name_'.$i.''],
              );
              FairHalls::create($multi_hall);
           }
        }
        return response()->json([
           'success' => true,
           'message' => 'Fair Hall Updated Successfully'
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $fair  = Fair::findOrFail($id);
        if ($fair) {
          $deleteFair = Fair::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Fair Delete Successfully'], 200);
        }
    }

    public function convertTimeZone($userTimeZone, $dateTime, $fairTimeZone){
        date_default_timezone_set($userTimeZone);
        $date = new \DateTime($dateTime, new \DateTimeZone($fair->timezone));
        $date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        // $data["start_date"] = $date->format('d M Y');
        // $data["start_time"] = $date->format('h:ia');
        $dateTime = $date->format('Y-m-d')." ".$date->format('H:i:s');

        return $dateTime;
    }

    private function getCandidateAddedWebinars($fair_id,$candidate_id){
      $webinarIds = [];
      $webinars = CandidateAgenda::where('candidate_id',$candidate_id)
                      ->where('fair_id',$fair_id)->get();
      if ($webinars) {
        foreach ($webinars as $key => $row) {
          array_push($webinarIds, $row->webinar_id);
        }
      }

      $addedWebinars = CompanyWebinar::whereIn('id',$webinarIds)
                                            ->where('fair_id',$fair_id)
                                            ->get();

      return $addedWebinars;
    }

    public function showFairByShortname(Request $request)
    {
        $fairTotalHalls = 1;
        $fairHallsData = [];
        $addedWebinars = [];
        $candidateTest = false;
        $candidate_id  = $request->candidate_id;
        $short_name    = $request->short_name;
        $timezone      =  $request->timezone;
        $fair = Fair::where('short_name',$short_name)->first();
        if ($fair) {
          $this->vistFairCandidates($fair,$request);
          if (!empty($candidate_id)) {
            $addedWebinars = $this->getCandidateAddedWebinars($fair->id,$candidate_id);
            $candidate = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair->id)->first();
            if ($candidate){
                $candidateTest = $candidate->is_take_test == 1 ? true : false;
            }else{
                FairCandidates::create(array(
                  'candidate_id'      => $candidate_id,
                  'fair_id'           => $fair->id,
                  'status'            => 'Active',
                  'marketing_channel' => 'Organic',
                  'source'            => 'Direct'
                ));
            }
          }else{
            $candidateTest = true;
          }

          $chatApiDetail = $this->organizerChatApiDetail($fair->organiser_id);

          // Get Fair halls Count And Their Data
          if ($fair->total_hall > 1) {
            $fairTotalHalls = $fair->total_hall;
            $halls   = FairHalls::where('fair_id',$fair->id)->orderBy('hall_id', 'ASC')->get();
            foreach ($halls as $key => $row) {
              $fairHallsData[] = [
                'hall_id'   => $row->hall_id,
                'hall_name' => $row->hall_name,
              ];
            }
          }

          $fair['fair_total_halls']  = $fairTotalHalls;
          $fair['fair_halls_data']   = $fairHallsData;
          $organizerName   = User::find($fair->organiser_id)->name;
          $organizerImage  = UserSettings::where('user_id',$fair->organiser_id)->select('user_image')->first();
          $fair['organiser_name']  =  $organizerName;
          $fair['organiser_image'] =  $organizerImage->user_image;

          date_default_timezone_set($timezone);
          //start time of fair
          $cur_date = new \DateTime(date('Y-m-d H:i:s'), new \DateTimeZone($fair->timezone));
          $cur_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
          $data["cur_date"] = $cur_date->format('Y-m-d')." ".$cur_date->format('H:i:s');

          $start_date = new \DateTime($fair->start_time, new \DateTimeZone($fair->timezone));
          $start_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
          $data["start_date"] = $start_date->format('d M Y');
          $data["start_time"] = $start_date->format('h:ia');
          $data["fair_start"] = $start_date->format('Y-m-d')." ".$start_date->format('H:i:s');

          //End time of fair
          $end_date = new \DateTime($fair->end_time, new \DateTimeZone($fair->timezone));
          $end_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
          $data["end_date"] = $end_date->format('d M Y');
          $data["end_time"] = $end_date->format('h:ia');
          $data["fair_end"] = $end_date->format('Y-m-d')." ".$end_date->format('H:i:s');

         $fairStartDateFrom = date('D jS F Y', strtotime($data["start_date"]));
         $fairStartTime = date('g:iA', strtotime($data["start_time"]));
         $fairEndTime = date('g:iA', strtotime($data["end_time"]));
         $date = date('Y-m-d H:i:s');

          //Registration time of fair
        $reg_date = new \DateTime($fair->register_time, new \DateTimeZone($fair->timezone));
        $reg_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $data["reg_date"] = $reg_date->format('d M Y');
        $data["reg_time"] = $reg_date->format('h:ia');
        $data["registration_date"] = $reg_date->format('Y-m-d')." ".$reg_date->format('H:i:s');
        $data["time-in-seconds"] = strtotime($data["fair_start"]) - strtotime($data["cur_date"]);

        $dateAndTimeArray = [
          'fair_id'              => $fair->id,
          'fair_start'           => $data["fair_start"],
          'fair_end'             => $data["fair_end"],
          'fair_start_date_from' => $fairStartDateFrom,
          'fair_start_time'      => $fairStartTime,
          'fair_end_time'        => $fairEndTime,
          'date'                 => $date,
        ];

        if($dateAndTimeArray['date'] >= $dateAndTimeArray['fair_start'] && $dateAndTimeArray['date'] <= $dateAndTimeArray['fair_end']){
          if (!empty($candidate_id)) {
            $res = CandidateTurnout::where('fair_id', $fair->id)->where('candidate_id',$candidate_id)->first();
            if (!$res) {
              CandidateTurnout::create(array(
                'candidate_id' => $candidate_id,
                'fair_id'      => $fair->id
              ));
            }
          }
        }

        if($dateAndTimeArray['date'] > $dateAndTimeArray['fair_end']){
          return response()->json([
             'error'    => true,
             'fairName' => $fair->name,
             'message'  => 'Fair Close'
          ], 200);
        }else{
          return response()->json([
            'fair'          => $fair,
            'dateAndTime'   => $dateAndTimeArray,
            'isTakeTest'    => $candidateTest,
            'addedWebinars' => $addedWebinars,
            'chatApiDetail' => $chatApiDetail,
          ]);
        }
           
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Fair Not Found'
            ], 404);
        }
    }

    public function terms($fair_id)
    {
        $terms = FairSetting::select('terms_conditions')->where('fair_id',$fair_id)->first();
        if ($terms) {
            return response()->json($terms);
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Terms And Condition Not Found'
            ], 404);
        }
    }

    public function privacy($fair_id)
    {
        $privacy = FairSetting::select('privacy_policy')->where('fair_id',$fair_id)->first();
        if ($privacy) {
            return response()->json($privacy);
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Privacy Policy Not Found'
            ], 404);
        }
    }

    public function aboutFair($fair_id)
    {
        $fair = FairSetting::where('fair_id',$fair_id)->first();
        if ($fair) {
            return response()->json(['info'=>$fair->fair_news,'terms'=>$fair->address],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Fair Not Found'
            ], 404);
        }
    }

    public function exhibitors($fair_id)
    {
        $companies = Company::select('id','company_logo','company_name')->where('fair_id',$fair_id)->get();
        if ($companies) {
            return response()->json(['companies'=>$companies],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Exhibitors Not Found'
            ], 404);
        }
    }

    public function jobs($fair_id)
    {
        $jobs = CompanyJob::where('fair_id',$fair_id)->with('company')->get();
        if ($jobs) {
            return response()->json(['jobs'=>$jobs],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Jobs Not Found'
            ], 404);
        }
    }
    public function matchingDetail($matching_param,$fair_id, $candidate_id, $where_id)
    {
  		$questions = CareerTest::where('fair_id', '=', $fair_id)->get();
  		foreach($questions as $question) {
  			$score = "question".$question->id;
				$data["$score"] = 0;
				$count = "questioncount".$question->id;
				$data["$count"] = 0;
  			$answers = CareerTestAnswer::where('test_id', '=', $question->id)->get();
  			foreach($answers as $answer) {
  				$index = "question".$question->id."answer".$answer->id;
  				$index2 = "chquestion".$question->id."chanswer".$answer->id;
    			$data["$index"] = 0;
    			$data["$index2"] = 0;
  			}
  		}

      if ($matching_param == 'job') {
        $matchingCriteria = JobQuestionnaire::where('job_id', '=', $where_id)->get();
      }elseif ($matching_param == 'webinar') {
        $matchingCriteria = WebinarQuestionnaire::where('webinar_id', '=', $where_id)->get();
      }elseif ($matching_param == 'recruiter'){
        $matchingCriteria = RecruiterQuestionnaire::where('recruiter_id', '=', $where_id)->get();
      }
  		foreach ($matchingCriteria as $criteria) {
  			$question_id = $criteria->test_id;
  			$answer = $criteria->answer;
  			$index2 = "chquestion".$question_id."chanswer".$answer;
  			$data["$index2"] = $criteria->score;
  		}
  		$candidate = CandidateTest::where('candidate_id', '=', $candidate_id)->where('fair_id','=', $fair_id)->get();
  		foreach($candidate as $test){
  			$question_id = $test->test_id;
  			$answer = $test->answer_id;
  			$index2 = "chquestion".$question_id."chanswer".$answer;
  			$score = "question".$question_id;
	      $data["$score"] = $data["$score"] + $data["$index2"];
  			$count = "questioncount".$question_id;
	      $data["$count"] = $data["$count"] + 1;
  		}
      $candidate = CandidateTest::where('candidate_id', '=', $candidate_id)->where('fair_id','=', $fair_id)->get();
  		foreach($candidate as $test){
  			$question_id = $test->test_id;
  			$answer = $test->answer_id;
  			$index2 = "chquestion".$question_id."chanswer".$answer;
  			if($data["$index2"] == 5){
    			$score = "question".$question_id;
				$data["$score"] = 5;
    			$count = "questioncount".$question_id;
				$data["$count"] = 1;
  			}
  		}
  		$questions = CareerTest::where('fair_id', '=', $fair_id)->get();
  		foreach($questions as $question) {
  			$score = "question".$question->id;
			  $count = "questioncount".$question->id;
        $match[] = array(
                    "question" => $question->question,
                    "score" => number_format(($data["$score"]/($data["$count"]*5))*100)
                  );
      }
      // $jobs = CompanyJob::where('fair_id',$fair_id)->with('company')->get();
      if ($match) {
          return response()->json(['match'=>$match], 200);
      }else{
          return response()->json([
             'error' => true,
             'message' => 'Invalid Match'
          ], 404);
      }
    }



    public function registeredCandidates($fair_id){
        $candidatesArr = [];
        $candidates    = FairCandidates::where('fair_id',$fair_id)->with('candidate','candidateInfo','candidateTest','candidateTurnout')
            ->orderBy("created_at",'DESC')
            ->groupBy('candidate_id')
            ->get();

        if ($candidates) {
            foreach ($candidates as $key => $value) {
                $candidatesArr[] = [
                    'candidate_id' => $value->candidate_id,
                    'name'         => empty($value->candidate) ? '' : $value->candidate->name,
                    'email'        => empty($value->candidate) ? '' : $value->candidate->email,
                    'country'      => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_country,
                    'user_image'   => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_image,
                    'city'         => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_city,
                    'cv'           => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_city,
                    'phone'        => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_phone,
                    'marketing_channel' => $value->marketing_channel,
                    'source'       => $value->source,
                    'status'       => $value->status,
                    'created_at'   => Carbon::createFromFormat('Y-m-d H:i:s',  $value->created_at)->format('F j, Y g:i A'),
                    'is_candidate_take_test'   => User::isCandidateTakeTest($fair_id,$value->candidate_id),
                    'is_candidate_in_hall'     => User::isCandidateInMainHall($fair_id,$value->candidate_id),
                    'is_candidate_attend_fair' => User::isCandidateAttendFair($fair_id,$value->candidate_id),
                    'is_candidate_online'      => User::isCandidateOnline($fair_id,$value->candidate_id)
                ];
            }
        }

       return $candidatesArr;
    }

    public function getTestId($answer_id){
      $test = CandidateTest::where('answer_id',$answer_id)->first();
      return $test->test_id;
    }

    public function answerEx($answer_id){
      $answer = CandidateTest::where('answer_id',$answer_id)->first();
      if ($answer) {
        return true;
      }

      return false;
    }

    public function registeredFilterCandidates(Request $request){
        $candidateIds = [];
        $fair_id = $request->fair_id;
        $answers = $request->answers;
        // return json_encode($request); die(); exit();
        // return $data; die;
        $questions = CareerTest::where('fair_id','=', $fair_id)->get();
        // $search = CandidateTest::whereNested(function($query) use ($answers,$fair_id) {
        //   foreach($answers as $answer){
        //     $query->orWhere('answer_id', '=', $answer);;
        //   }
        // })->where('fair_id', '=', $fair_id)->select('candidate_id')->get();
        $candidates_list = array();
    		$loop = 0;
    		foreach($questions as $question){
    			$id = $question->id;
  				$loop++;
  				$c = CandidateTest::where('fair_id', '=', $fair_id)->where('test_id','=', $id)->whereIn('answer_id', $answers)->select('candidate_id')->get();
  				foreach($c as $candidate){
  					$candidates_list[$loop][] = $candidate->candidate_id;
  				}
    		}
        // return reset($candidates_list); die;
    		if(count($candidates_list) == 1){
          $newArr = reset($candidates_list);
          $candidates_list = array();
          $candidates_list[0]  = $newArr;
    			$candidates_list[1]  = $newArr;
    		}

    		// return $candidates_list;
    		$search = call_user_func_array('array_intersect', $candidates_list);

        // return $search; die;
        //return json_encode($filterOptions);
        if($search){
          $candidates = User::whereNested(function($query) use ($search) {
                      foreach ($search as $key => $value)
                          {
                              $query->orWhere('id','=', $value);
                          }
                  })
                  ->orderBy('id', 'Desc')->groupBy('id')->get();
        }else{
          $candidates = [];
        }
        $count = count($candidates);
        $candidateIds = array();
        foreach ($candidates as $key => $candidate) {
          $candidateIds[] = $candidate->id;
        }
        // foreach ($answers as $key => $value) {
        //     $search = CandidateTest::where('answer_id',$value)->where('fair_id',$fair_id)->select('candidate_id')->distinct('candidate_id')->get();
        //     array_push($candidateIds,$search);
        // }
        // return $candidateIds; die;
        // $search = CandidateTest::where('fair_id')
        $candidatesArr = [];
        $candidates    = FairCandidates::where('fair_id',$fair_id)->whereIn('candidate_id',$candidateIds)->with('candidate','candidateInfo','candidateTest','candidateTurnout')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($candidates) {
            foreach ($candidates as $key => $value) {
                $candidatesArr[] = [
                    'candidate_id' => $value->candidate_id,
                    'name'         => empty($value->candidate) ? '' : $value->candidate->name,
                    'email'        => empty($value->candidate) ? '' : $value->candidate->email,
                    'country'      => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_country,
                    'city'         => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_city,
                    'cv'           => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_city,
                    'phone'        => empty($value->candidateInfo) ? '' : $value->candidateInfo->user_phone,
                    'marketing_channel' => $value->marketing_channel,
                    'source'       => $value->source,
                    'status'       => $value->status,
                    'created_at'   => date('d-m-Y', strtotime($value->created_at)),
                    'is_candidate_take_test'   => User::isCandidateTakeTest($fair_id,$value->candidate_id),
                    'is_candidate_attend_fair' => User::isCandidateAttendFair($fair_id,$value->candidate_id),
                    'is_candidate_online'      => User::isCandidateOnline($fair_id,$value->candidate_id)
                ];
            }
        }

       return $candidatesArr;
    }

  // Regenerate Candidate Matching Jobs, Recruiters, And Webinars
  public function cacheClear($fair_id){
    $candidates = FairCandidates::where('fair_id',$fair_id)->orderBy('id', 'desc')->get();
    // return $candidates; die;
    if ($candidates) {
       foreach ($candidates as $candidate) {
          if ($candidate->is_take_test == 1) {
            $Test = CandidateTest::where('candidate_id',$candidate->candidate_id)->where('fair_id',$fair_id)->get();
             if ($Test->count() > 0) {
              // echo "ID ".$candidate->candidate_id."<br/>";
              $this->generateMatchingJobs($candidate->candidate_id,$fair_id);
              $this->generateMatchingRecruiters($candidate->candidate_id,$fair_id);
              $this->generateMatchingWebinars($candidate->candidate_id,$fair_id); 
             }
          }
      }

      return response()->json([
        'success' => true,
        'message' => 'Fair Cache Regenerated Successfully'
      ], 200); 
    }else{
      return response()->json([
        'error' => true,
        'message' => 'Fair Candidates Not Found'
      ], 404); 
    }
  }

  
}
