<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use AWS;
use App\Http\Requests\StoreCandidate;
use Carbon\Carbon;
use App\User;
use App\MatchJob;
use App\MatchWebinar;
use App\CareerTest;
use App\CareerTestAnswer;
use App\UserSettings;
use App\Role;
use App\CandidateTest;
use App\CandidateJob;
use App\MatchRecruiter;
use App\Traits\MatchingJobs;
use App\Traits\MatchingRecruiters;
use App\Traits\MatchingWebinars;
use App\Traits\RecruiterCandidates;
use App\Traits\CandidateEmail;
use App\Traits\CandidatePersonalAgenda;
use App\MarketingChannel;
use App\MarketingRegistration;
use App\ChatTranscript;
use App\FairCandidates;
use App\CandidateAgenda;

class CandidateController extends Controller
{
    use MatchingJobs, MatchingRecruiters, MatchingWebinars, RecruiterCandidates, CandidatePersonalAgenda,CandidateEmail;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $userObject = (object) [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'fair_id'   => $user->userSetting['fair_id'],
            'country_name' => $user->userSetting['user_country'],
            'city_name'    => $user->userSetting['user_city'],
            'postal_code'  => $user->userSetting['user_postal_code'],
            'phone'        => $user->userSetting['phone'],
            'cv'           => $user->userSetting['user_cv'],
            'profile_image'=> $user->userSetting['user_image']
        ];

        return $userObject;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCandidate $request)
    {  
        $validated = $request->validated();
        $user_id = '';
        $userObject = '';
        $data = $request->all();
        $role = Role::IsRoleExist($data['role']);
        if($role){
            $user = User::create([
              'name'           => $data['name'],
              'email'          => $data['email'],
              'password'       => bcrypt($data['password']),
              'plan_password'  => $data['password'],
            ]);
            $user->roles()->attach($role);
            $userCV = '';
            if($request->hasFile('document')){
                  $file = $request->file('document');
                  $filename = rand().'_'.$file->getClientOriginalName();
                  $path = 'candidate_cv/';
                  $target_file = $path.$filename;
                  // echo env('S3_PRIVATE_EP'); die;
                  // $path = public_path().'/documents/';
                  // $move = $file->move($path, $filename);
                  Storage::disk('resumes_s3')->put($target_file, file_get_contents($file), 'private');
                  // $userCV = $filename;
            }
             
           
            $userObject = $user;
            $user_id = $user->id;
            $user = UserSettings::create([
                'user_id'          => $user_id,
                'fair_id'          => $data['fair_id'],
                'user_country'     => $data['user_country'],
                'phone'            => $data['phone'],
                'user_city'        => $data['user_city'],
                'user_postal_code' => $data['user_postal_code'],
                'user_cv'          => $userCV,
             ]);

            if (!empty($data['channel'])) {
              $channel = MarketingChannel::where('channel_name',$data['channel'])
                            ->where('fair_id',$data['fair_id'])
                            ->select('id')
                            ->first();  
              $marketingRegistration = MarketingRegistration::create([
                'channel_id' => $channel->id,
                'user_id'    => $user_id
              ]);
                FairCandidates::create(array(
                  'candidate_id'      => $user_id,
                  'fair_id'           => $data['fair_id'],
                  'status'            => 'Active',
                  'marketing_channel' => $data['channel'],
                  'source'            => 'Direct'
                ));
              }else{
                FairCandidates::create(array(
                  'candidate_id'      => $user_id,
                  'fair_id'           => $data['fair_id'],
                  'status'            => 'Active',
                  'marketing_channel' => 'Organic',
                  'source'            => 'Direct'
                ));
              }


           if ($user) {
              // Generate Email For Candidate
              $this->generateEmail($request,$user->id);
              $user = User::find($userObject->id);
              $credentials = ['email'=>$user->email, 'password'=>$user->plan_password];
              if(!Auth::attempt($credentials))
                  return response()->json([
                    "code"   => 401,
                    "status" => "Unauthorized",
                  ], 401);
              $user = $request->user();
              $userObject = $this->show($user->id);
              $tokenResult = $user->createToken('Personal Access Token');
              $token = $tokenResult->token;
              if ($request->remember_me)
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();
              return response()->json([
                  "code"         => 200,
                  "status"       => "success",
                  'access_token' => $tokenResult->accessToken,
                  'token_type'   => 'Bearer',
                  'user'         =>  $userObject,
                  'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
              ], 200);
              
              // return response()->json([
              //     'success' => true,
              //     'message' => $data['role'].' Created Successfully',
              //     'registerUserObject'=>$userObject
              // ],200); 
           }else{
                return response()->json([
                   'error' => true,
                   'message' => $data['role'].' Not Created Successfully'
                ], 401);
            }
            
        }else{
           return response()->json([
               'error' => true,
               'message' => 'User Role Not Find'
            ], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeCareerTest(Request $request)
    {  
        $answers = $request->selectedAnswers;
        $fair_id = $request->fair_id;
        $candidate_id = $request->candidate_id;
       CandidateTest::where('candidate_id',$request->candidate_id)->where('fair_id',$fair_id)->delete();
        foreach ($answers as $key => $row) {
            CandidateTest::create([
              'candidate_id'=> $candidate_id,
              'fair_id'     => $fair_id,
              'test_id'     => $this->getTestId($key),
              'answer_id'   => $key
            ]);   
        }

        FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->update(['is_take_test'=>1]);

        $this->generateMatchingJobs($candidate_id,$fair_id);
        $this->generateMatchingRecruiters($candidate_id,$fair_id);
        $this->generateMatchingWebinars($candidate_id,$fair_id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMatchingJobs(Request $request, $candidate = '')
    {  
      $fair_id      = $request->fair_id;
      $candidate_id = $request->candidate_id;
      if ($candidate == 'true') {
        $jobs = MatchJob::where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id)
                        ->with('jobDetail')
                        ->orderBy('percentage', 'Desc')
                        ->get();
        $candidateAppliedJobs = CandidateJob::where('candidate_id',$candidate_id)
                        ->with('job')
                        ->where('fair_id',$fair_id)
                        ->get();

        return response()->json(['jobs'=>$jobs,'appliedJobs'=>$candidateAppliedJobs]);
      }

      $fair_id      = $request->fair_id;
      $candidate_id = $request->candidate_id;
      $jobs = MatchJob::where('candidate_id',$candidate_id)
                      ->where('fair_id',$fair_id)
                      ->with('jobDetail','companyDetail')
                      ->orderBy('percentage', 'Desc')
                      ->get();
      $candidateAppliedJobs = CandidateJob::where('candidate_id',$candidate_id)
                              ->where('fair_id',$fair_id)->get();

      return response()->json(['jobs'=>$jobs,'appliedJobs'=>$candidateAppliedJobs]);
        
    }


    public function getMatchingRecruiters(Request $request)
    {  
        $recruitersArr = [];
        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $recruiters = MatchRecruiter::where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id)
                        ->with('recruiter','companyDetail','recruiterSetting')
                        ->orderBy('percentage', 'Desc')
                        ->get();
        foreach ($recruiters as $row) {
            $recruitersArr[] = [
                "id"         => $row->recruiter_id,
                "company_id" => $row->company_id,
                "fair_id"    => $row->fair_id,
                "percentage" => $row->percentage,
                'name'       => $row->recruiter->name,
                'company_name'   => $row->companyDetail->company_name,
                'title'          => $row->recruiterSetting->user_title,
                'public_email'   => $row->recruiterSetting->public_email,
                'linkedin'       => $row->recruiterSetting->linkedin_profile_link,
                'recruiter_img'  => $row->recruiterSetting->recruiter_img,
                'user_image'  => $row->recruiterSetting->user_image,
                'location'       => $row->recruiterSetting->location,
            ];
        }
        return response()->json(['recruiters'=>$recruitersArr]);

    }

    public function getMatchingWebinars(Request $request)
    {  
      $fair_id      = $request->fair_id;
      $candidate_id = $request->candidate_id;
      $webinars = MatchWebinar::where('candidate_id',$candidate_id)
                      ->where('fair_id',$fair_id)
                      ->with('companyWebinar','companyDetail')
                      ->orderBy('percentage', 'Desc')
                      ->get();
      $addedWebinars = CandidateJob::where('candidate_id',$candidate_id)
                          ->where('fair_id',$fair_id)->get();
      return response()->json(['webinars'=>$webinars,'addedWebinars'=>$addedWebinars]);
    }

    public function applyJob(Request $request){
        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $job_id       = $request->job_id;
        $company_id   = $request->company_id;
        $candidateJob = CandidateJob::where('candidate_id',$candidate_id)->where('job_id',$job_id); 
        if($candidateJob->exists()){
            $candidateJob->delete();
        }else{
            CandidateJob::create(array(
                'candidate_id' => $candidate_id,
                'job_id'       => $job_id,
                'fair_id'      => $fair_id,
                'company_id'   => $company_id
            ));

            $this->jobApplyEmail($request);

        }
        $candidateAppliedJobs = CandidateJob::where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id)->get();
        return $candidateAppliedJobs;
    }

     public function addWebinar(Request $request){
        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $webinar_id       = $request->webinar_id;
        $company_id   = $request->company_id;
        $CandidateAgenda = CandidateAgenda::where('candidate_id',$candidate_id)->where('webinar_id',$webinar_id)->first(); 
        if($CandidateAgenda){
            $CandidateAgenda->delete();
        }else{
            CandidateAgenda::create(array(
                'candidate_id' => $candidate_id,
                'webinar_id'   => $webinar_id,
                'fair_id'      => $fair_id,
                'webinar_type' => 'Webinar'
            ));
        }
        $candidateAddedWebinars = CandidateAgenda::where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id)->get();
        return $candidateAddedWebinars;
    }

    public function upProfileImage(Request $request){
        if($request->hasFile('profile_image')){
          $file = $request->file('profile_image');
          $filename = rand().'_'.$file->getClientOriginalName();
          $path = 'assets/candidate_avatars/';
          $target_file = $path.$filename;
          // echo env('S3_PRIVATE_EP'); die;
          // $path = public_path().'/documents/';
          // $move = $file->move($path, $filename);
          Storage::disk('s3')->put($target_file, file_get_contents($file), 'public');
          $profileImage = $filename;
          UserSettings::where('user_id',$request->candidate_id)->update(['user_image'=>$profileImage]);
          $user = User::find($request->candidate_id);
           $userObject = $this->show($request->candidate_id);
            return response()->json([
                "code"         => 200,
                "status"       => "success",
                'user'         =>  $userObject,
            ], 200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Please Upload File'
            ], 401); 
        }
    }

    public function updateResume(Request $request){
        if($request->hasFile('resume')){
          $file = $request->file('resume');
          $filename = rand().'_'.$file->getClientOriginalName();
          $path = 'assets/candidate_cv/';
          $target_file = $path.$filename;
          // echo env('S3_PRIVATE_EP'); die;
          // $path = public_path().'/documents/';
          // $move = $file->move($path, $filename);
          Storage::disk('s3')->put($target_file, file_get_contents($file), 'public');
          $resume = $filename;
          UserSettings::where('user_id',$request->candidate_id)->update(['user_cv'=>$resume]);
          $user = User::find($request->candidate_id);
          $userObject = $this->show($request->candidate_id);
            return response()->json([
                "code"         => 200,
                "status"       => "success",
                'user'         =>  $userObject,
            ], 200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Please Upload File'
            ], 401); 
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {      
        $id    = $request->id;
        $user  = User::findOrFail($id);
        if (!empty($user)) {
            $userDataToUpdate = [
              'name'          => $request->name,
            ];
            $user->update($userDataToUpdate);
        }
        $setting = UserSettings::where('user_id', $id);
        if (!empty($setting)) {
            $settingDataToUpdate = [
                'user_country'     => $request->country_name,
                'user_city'        => $request->city_name,
                'phone'            => $request->phone,
                'user_postal_code' => $request->postal_code
           ];
           $setting->update($settingDataToUpdate);
           if ($setting) {
              $user = User::find($request->candidate_id);
               $userObject = $this->show($id);
                return response()->json([
                    "code"         => 200,
                    "status"       => "success",
                    'user'         =>  $userObject,
                ], 200);
            }
        }    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $careerTest = CareerTest::all()->where('fair_id',$fair_id);
    }

    // Candidate Career Test List
    public function getCareerTestList($candidate_id,$fair_id){
        $arr = [];
        $careerTest = CandidateTest::all()->where('candidate_id',$candidate_id)->where('fair_id',$fair_id);
        if ($careerTest->isEmpty()) {
            return response()->json([
               'error' => true,
               'message' => 'Career Test Not Found'
            ], 401);
        }else{
            foreach ($careerTest as $key => $value) {
                $arr[] = [
                    'answer_id' => $value['answer_id']
                ];
            }
           return response()->json($arr);
        }
        
    }

    // Get Career Test ID
    private function getTestId($answer_id){
        $answer = CareerTestAnswer::where('id',$answer_id)->first();
        if ($answer) {
            return $answer->test_id;
        }   
    }


    public function userChats(Request $request){
        $company_id = $request->company_id;
        $fair_id    = $request->fair_id;
        $id         = $request->user_id; 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://api.cometondemand.net/api/v2/getMessages");
        curl_setopt($ch, CURLOPT_HTTPHEADER,array(
          'api-key: 51374xb73fca7c64f3a49d2ffdefbb1f2e8c76'
        ));
        curl_setopt($ch, CURLOPT_POST, 1);  
        curl_setopt($ch, CURLOPT_POSTFIELDS,'limit=5000&UIDs='.$id);
        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $apiResponse = curl_exec($ch);
        $apiResponse = (array)json_decode($apiResponse);
        curl_close ($ch);

        foreach ($apiResponse['success']->data as $key => $data) {
          // print_r($data);exit;
          if($data){
            foreach ($data as $key => $dataa) {

              if($dataa != "No chats available"){
                foreach ($dataa as $key => $dataaa) {
                  if(!ChatTranscript::where('id', '=', $dataaa->message_id)->exists()){
                    if($dataaa->sender_uid > 0 && $dataaa->reciever_uid > 0){
                      ChatTranscript::create(array(
                            'id'         =>  $dataaa->message_id,
                            'from'       =>  $dataaa->sender_uid,
                            'to'         =>  $dataaa->reciever_uid,
                            'message'    =>  $dataaa->message,
                            'sent'       =>  $dataaa->timestamp,
                            'fair_id'    =>  $fair_id,
                            'company_id' =>  $company_id
                      ));
                    }
                    //echo $dataaa->message_id."<br>";
                  }
                }
              }
            }
          }
        }
        $chats = ChatTranscript::where('from','=', $id)->orWhere('to','=', $id)->groupBy('to')->with('userFrom','userTo')->get();
        $user = User::find($id)->first_name;

        return response()->json(['user'=>$user,'chats'=>$chats]); 
    }

    public function chatConversation($one, $two, $user_id)
    {
      $conversations = ChatTranscript::orWhere(function($query) use ($one, $two){
                        $query->where('from', '=', $one)
                          ->where('to', '=', $two);
                        })->orWhere(function($query) use ($one, $two){
                          $query->where('to', '=', $one)
                            ->where('from', '=', $two);
                        })->orderBy('id','asc')->with('userFrom')->get();
      $data["user"]    = User::find($user_id)->first_name;
      $data["user_id"] = $user_id;
      if($one != $user_id){
        $data["candidate"]    = User::find($one)->first_name;
        $data["candidate_id"] = $one;
      }
      if($two != $user_id){
        $data["candidate"]    = User::find($two)->first_name;
        $data["candidate_id"] = $two;
      }

      return response()->json(['conversations'=>$conversations,'userid'=>$user_id,'data'=>$data]); 
    }

    public function personalAgenda(Request $request){
      $agend = $this->getCandidatePersonalAgenda($request);
      return $agend;
    }


   public function blockCandidate($candidate_id, $fair_id){
      $candidate = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->first();
      if ($candidate) {
        $updateStatus = $candidate->update(['status'=>'Block']);
        if ($updateStatus) {
          $this->blockCandidateEmail($fair_id, $candidate_id);
          return response()->json([
           'success' => true,
           'message' => 'Candidate Blocked Successfully'
          ], 200); 
        }
      }
      return response()->json([
         'error' => true,
         'message' => 'Candidate Not Found'
        ], 404);
   }

    public function unBlockCandidate($candidate_id, $fair_id){
      $candidate = FairCandidates::where('candidate_id',$candidate_id)->where('fair_id',$fair_id)->first();
      if ($candidate) {
        $updateStatus = $candidate->update(['status'=>'Active']);
        if ($updateStatus) {
          return response()->json([
           'success' => true,
           'message' => 'Candidate UnBlocked Successfully'
          ], 200); 
        }
      }

      return response()->json([
       'error'   => true,
       'message' => 'Candidate Not Found'
      ], 404);
    }
    // Block Multipal Candidates
    public function blukBlockCandidates(Request $request){
      $ids  = $request->ids;
      $fair_id = $request->fair_id;
      for ($i=0; $i < count($ids) ; $i++) {
        $user = FairCandidates::where('candidate_id',$ids[$i])->where('fair_id',$fair_id)->first();
        if ($user) {
          $user->update(['status'=>'Block']);
          $this->blockCandidateEmail($fair_id, $ids[$i]);
        }
          // $candidate = User::find($ids[$i]);
          // $name = $candidate->firstname;
          // $email = $candidate->email;
          // $fair = Fairs::find($fair_id);
          // $fairname = $fair->name;
          // $candidate_id = $candidate->id;
          // $emails = FairCandidates::where('candidate_id', '=', $candidate->id)->where('fair_id', '=', $fair_id)->first();
          // $faircandidate_id = $emails->id;
          // if($emails->email_notification == 1){
          //   Mail::send('emails.block', ['name' => $name, 'email' => $email, 'faircandidate_id' => $faircandidate_id, 'candidate_id' => $candidate_id, 'fairname' => $fairname], function($message) use ($email,$name, $fairname)
          //   {
          //       $message->to($email, $name)->subject('Thank you for your interest in the '.$fairname);
          //   });
          // }
          // Session::flash('success', 'Candidate blocked successfully');
          // return Redirect::back();
      }

      return response()->json([
              'success' => true,
              'message' => 'Selected Candidates Blocked Successfully'
      ], 200);

       
    }
    // reset candidate password from admin panel
    public function resetCandidatePassword($candidate_id){
      $newPassword = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(10/strlen($x)) )),1, 10);
      $user = User::find($candidate_id);
      if ($user) {
        $user->password =  bcrypt($newPassword);
        $user->save();
        return response()->json([
         'success'   => true,
         'data'      => $newPassword,
         'message'   => 'Candidate Password Updated Successfully'
        ], 200);
      }

      return response()->json([
       'error'   => true,
       'message' => 'Candidate Password Not Updated'
      ], 401);

  }

  // Update Mailhall value if candidate enter in maill hall
  public function inHall($fair_id,$candidate_id){
    FairCandidates::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->update(['mainhall' => 1]);
  }

  public function deleteCandidate($candidate_id,$fair_id){
    User::find($candidate_id)->delete();
    UserSettings::find($candidate_id)->delete();
    $fairCandidateDelete = FairCandidates::where('fair_id',$fair_id)->where('candidate_id',$candidate_id)->delete();
    if ($fairCandidateDelete) {
      return response()->json([
        'success'   => true,
        'message'   => 'Candidate Deleted Successfully'
      ], 200);
    }

    return response()->json([
     'error'   => true,
     'message' => 'Candidate Not Deleted'
    ], 401);
  }

  public function downloadCV($candidate_id){
    $user = User::find($candidate_id)->load('userSetting');
    return response()->json($user->userSetting->user_cv);
  }
    
}
