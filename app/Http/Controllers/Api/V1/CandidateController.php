<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
class CandidateController extends Controller
{
    use MatchingJobs, MatchingRecruiters, MatchingWebinars;
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
            'first_name'=> $user->first_name,
            'last_name' => $user->last_name,
            'email'     => $user->email,
            'fair_id'   => $user->userSetting['fair_id'],
            'country_name' => $user->userSetting['user_country'],
            'city_name'    => $user->userSetting['user_city'],
            'postal_code'  => $user->userSetting['user_postal_code'],
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
    public function store(Request $request)
    {  
        $user_id = '';
        $userObject = '';
        $data = $request->all(); 
        $role = Role::IsRoleExist($data['role']);
        if($role){
            $user = User::create([
              'first_name'=> $data['first_name'],
              'last_name' => $data['last_name'],
              'name'      => $data['first_name'].' '.$data['last_name'],
              'email'     => $data['email'],
              'password'  => bcrypt($data['password']),
              'plan_password' => $data['password']
            ]);
            $user->roles()->attach($role);
            $userCV = '';
            if($request->hasFile('document')){
                  $file = $request->file('document');
                  $filename = $file->getClientOriginalName();
                  $path = public_path().'/documents/';
                  $move = $file->move($path, $filename);
                  $userCV = $filename;
            }
            $userObject = $user;
            $user_id = $user->id;
            $user = UserSettings::create([
                'user_id'          => $user_id,
                'fair_id'          => $data['fair_id'],
                'user_country'     => $data['user_country'],
                'user_city'        => $data['user_city'],
                'user_postal_code' => $data['user_postal_code'],
                'user_cv'          => $userCV,
             ]);

           if ($user) {
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
    public function getMatchingJobs(Request $request)
    {  

        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $jobs = MatchJob::where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id)
                        ->with('jobDetail','companyDetail')
                        ->orderBy('percentage', 'Desc')
                        ->get();
        $candidateAppliedJobs = CandidateJob::all()
                                ->where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id);

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
      $addedWeninars = CandidateJob::all()
                        ->where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id);

      return response()->json(['webinars'=>$webinars,'addedWeninars'=>$addedWeninars]);

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
        }
        $candidateAppliedJobs = CandidateJob::all()
                                ->where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id);
        return $candidateAppliedJobs;
    }

    public function addWebinar(Request $request){
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
        }
        $candidateAppliedJobs = CandidateJob::all()
                                ->where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id);
        return $candidateAppliedJobs;
    }

    public function upProfileImage(Request $request){
        if($request->hasFile('profile_image')){
          $file = $request->file('profile_image');
          $filename = $file->getClientOriginalName();
          $path = public_path().'/candidate/images/';
          $move = $file->move($path, $filename);
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
              'first_name'    => $request->first_name,
              'last_name'     => $request->last_name,
              'name'          => $request->first_name.' '.$request->last_name,
            ];
            $user->update($userDataToUpdate);
        }
        $setting = UserSettings::where('user_id', $id);
        if (!empty($setting)) {
            $settingDataToUpdate = [
                'user_country'     => $request->country_name,
                'user_city'        => $request->city_name,
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
}
