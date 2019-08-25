<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\CareerTest;
use App\CareerTestAnswer;
use App\UserSettings;
use App\Role;
use App\CandidateTest;
class CandidateController extends Controller
{
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
        $user = User::whereHas('roles', function ($query) use ($id) {
            $query->where('name', '=', 'User');
        })->with('userSetting')->where('id',$id)->get();

        if (!empty($user)) {
            return response()->json($user);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'We not find user in our database'
            ], 401);
        }

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
            $userObject = $user;
            $user_id = $user->id;
            $user = UserSettings::create([
                'user_id'          => $user_id,
                'fair_id'          => $data['fair_id'],
                'user_info'        => empty($data['user_info']) ? '': $data['user_info'],
                'user_skype'       => $data['user_skype'],
                'user_city'        => $data['user_city'],
                'user_country'     => $data['user_country'], 
                'user_postal_coe'  => $data['user_postal_code'],
                'user_cv'          => $data['user_cv'],
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
                    'user'         =>  $user,
                    'expires_at'   => Carbon::parse(
                        $tokenResult->token->expires_at
                    )->toDateTimeString()
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
       CandidateTest::where('candidate_id',$request->candidate_id)->delete();
       $answers = $request->selectedAnswers;
       $fair_id = $request->fair_id;
       $candidate_id = $request->candidate_id;
       foreach ($answers as $key => $row) {
           CandidateTest::create([
            'candidate_id'=> $candidate_id,
            'fair_id'     => $fair_id,
            'test_id'     => $this->getTestId($key),
            'answer_id'   => $key
           ]); 
        }

        return response()->json([
            'success' => true,
            'message' => 'Career Test Saved Successfully',
        ],200); 
        
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
        //
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

    public function getCareerTestList($fair_id){
        $careerTest = CareerTest::all()->where('fair_id',$fair_id);
        $questionsArr = [];
        foreach ($careerTest as $key => $value){
            $questionsArr[]=[
                "id"                 =>$value->id,
                "fair_id"            =>$value->fair_id,
                "question"           =>$value->question,
                "short_question"     =>$value->short_question,
                "backoffice_question"=>$value->backoffice_question,
                "question_type"      =>$value->question_type,
                "min_selection"      =>$value->min_selection,
                "max_selection"      =>$value->max_selection,
                "display_order"      =>$value->display_order,
                "answers"            => $this->answers($value->id)
            ];
        }

        return response()->json($questionsArr);
    }

    private function answers($test_id){
        $answers = CareerTestAnswer::all()->where('test_id',$test_id);
        $answersArr = [];
        foreach ($answers as $key => $value){
            $answersArr[] = [
                "id"      => $value->id,
                "test_id" => $value->test_id,
                "answer"  => $value->answer,
                "is_checked"=>false
            ];
        }

        return $answersArr;
    }

    private function getTestId($answer_id){
        $answer = CareerTestAnswer::where('id',$answer_id)->first();
        if ($answer) {
            return $answer->test_id;
        }
        
    }
}
