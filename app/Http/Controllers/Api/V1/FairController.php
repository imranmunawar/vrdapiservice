<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Fair;
use App\Company;
use App\CompanyJob;
use App\FairSetting;
use App\CareerTest;
use App\CandidateTest;
use App\UserSettings;
use App\Traits\TrackCandidates;
use App\Traits\FairLiveEmailNotification;
use App\Traits\FairEndEmailCandidates;
use App\FairCandidates;
use App\CandidateAgenda;
use App\CompanyWebinar;
use \Input as Input;
use DB;

class FairController extends Controller
{
    use TrackCandidates,FairLiveEmailNotification,FairEndEmailCandidates;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    {
        $fairs = Fair::with('organizer')->get();
        return response()->json($fairs);
    }

    public function testRoute(){
        echo env('S3_PRIVATE_EP'); die;
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
        if (!$fair) {
            return response()->json(['success' => false,'message' => 'Fair Media Not Created Successfully'],200); 
        }
        return response()->json(['success' => true,'message' => 'Fair Media Created Successfully' ],200);
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
          array_push($webinarIds, $row->id);
        }
      }

      $addedWebinars = CompanyWebinar::whereIn('id',$webinarIds)
                                            ->where('fair_id',$fair_id)
                                            ->get();

      return $addedWebinars;
    }

    public function showFairByShortname(Request $request)
    {
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
            'fair_start'           => $data["fair_start"],
            'fair_end'             => $data["fair_end"],
            'fair_start_date_from' => $fairStartDateFrom,
            'fair_start_time'      => $fairStartTime,
            'fair_end_time'        => $fairEndTime,
            'date'                 => $date,
        ];
            return response()->json(['fair'=>$fair,'dateAndTime'=>$dateAndTimeArray,'isTakeTest'=>$candidateTest,'addedWebinars'=>$addedWebinars]);

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

    public function aboutFair($organizer_id)
    {
        $organizer = UserSettings::where('user_id',$organizer_id)->first();
        if ($organizer) {
            return response()->json(['info'=>$organizer->user_info],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Fair Not Found'
            ], 404);
        }    
    }

    public function exhibitors($fair_id)
    {
        $companies = Company::select('company_logo','company_name')->where('fair_id',$fair_id)->get();
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

    public function registeredCandidates($fair_id){
        $candidatesArr = [];
        $candidates    = FairCandidates::where('fair_id',$fair_id)->with('candidate','candidateInfo','candidateTest','candidateTurnout')
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
        // return $data; die;
        $questions = CareerTest::where('fair_id','=', $fair_id)->get();
        $search = CandidateTest::whereNested(function($query) use ($answers,$fair_id) {
          foreach($answers as $answer){
            $query->orWhere('answer_id', '=', $answer);;
          }
        })->where('fair_id', '=', $fair_id)->select('candidate_id')->get();

        // return $search; die;
        //return json_encode($filterOptions);
        if(!$search->isEmpty()){
          $candidates = User::whereNested(function($query) use ($search) {
                      foreach ($search as $key => $value)
                          {
                              $query->orWhere('id','=', $value["candidate_id"]);
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
                ];
            }
        }
      
       return $candidatesArr;
    }

}
