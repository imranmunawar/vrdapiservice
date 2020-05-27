<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\UserSettings;
use App\Fair;
use App\CometChatPro;
use App\Company;
use DB;
class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed'
        ]);
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->save();
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }
  
    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function backendLogin(Request $request)
    {
        if ($this->validateRole($request->email)) {
           $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
           ]);
            $credentials = request(['email', 'password']);
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
                "code"   => 200,
                "status" => "success",
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'user'=>$user,
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ], 200);  
        }else{
            return response()->json([
                "code"   => 401,
                "status" => "Unauthorized",
            ], 401);
        }
        
    }

    public function frontLogin(Request $request)
    {
        if($this->isRoleUser($request->email)){
            $request->validate([
                'email'       => 'required|string|email',
                'password'    => 'required|string',
                'remember_me' => 'boolean'
            ]);
            $credentials = request(['email', 'password']);
            if(!Auth::attempt($credentials))
                return response()->json([
                    "code"   => 401,
                    "status" => "Unauthorized",
                ], 401);
            $user = $request->user();
           $userObject = (object) [
                'id'        => $user->id,
                'name'      => $user->name,
                'first_name'=> $user->first_name,
                'last_name' => $user->last_name,
                'email'     => $user->email,
                'fair_id'   => $user->userSetting['fair_id'],
                'phone'     => $user->userSetting['phone'],
                'country_name' => $user->userSetting['user_country'],
                'city_name'    => $user->userSetting['user_city'],
                'postal_code'  => $user->userSetting['user_postal_code'],
                'cv'           => $user->userSetting['user_cv'],
                'profile_image'=> $user->userSetting['user_image']

            ];
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
                'expires_at'   => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString()
            ], 200);
        }else{
            return response()->json([
                "code"   => 401,    
                "status" => "Unauthorized",
            ], 401); 
        }
    }

    private function validateRole($email){
       $user     = User::where('email',$email)->first();
        if (empty($user)) {
            return false;
        }else{
           $userRole = DB::table('role_user')->where('user_id', '=', $user->id)->first();
           $roleName = DB::table('roles')->where('id', '=', $userRole->role_id)->first(); 
           if ($roleName->name == 'Admin' || $roleName->name == 'Organizer' || $roleName->name == 'Receptionist' || $roleName->name ==  'Company Admin' || $roleName->name ==  'Recruiter') {
               return true;
           }

           return false;
        } 
    }

    private function isRoleUser($email){
        $user     = User::where('email',$email)->first();
        if (empty($user)) {
            return false;
        }else{
           $userRole = DB::table('role_user')->where('user_id', '=', $user->id)->first();
           $roleName = DB::table('roles')->where('id', '=', $userRole->role_id)->first(); 
           if ($roleName->name == 'User') {
               return true;
           }

           return false;
        }
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            "code"   => 200,
            "status" => "success",
            'message' => 'Successfully logged out'
        ]);
    }

    public function getCometChatApiDetail($organizerId){
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
        $appId      = $cometApiDetail->app_id;
        $appKey     = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region     = $cometApiDetail->region;

        $detail = [
            'appId'      => $appId,
            'appKey'     => $appKey,
            'restApiKey' => $restApiKey,
            'region'     => $region
        ];

        return $detail;

    }
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $userArr     = []; 
        $role        = $request->user()->roles->first()->name;
        $userId      = $request->user()->id;
        $userName    = $request->user()->name;
        $userEmail   = $request->user()->email;
        $userArr['role']               = $role;
        $userArr['userId']             = $userId;
        $userArr['userName']           = $userName;
        $userArr['userEmail']          = $userEmail;

        if ($role == 'Recruiter' || $role == 'Company Admin') {
            $userSetting = UserSettings::where('user_id',$userId)->select('fair_id','company_id','recruiter_status')->first();
            UserSettings::where('user_id',$userId)->update(['recruiter_status'=>'online']);
            $userFairId      = $userSetting->fair_id;
            $organiserId     = $userSetting->organiser_id;
            $companyId       = $userSetting->company_id;
            $recruiterStatus = $userSetting->recruiter_status;
            $userArr['userFairId']         = $userFairId;
            $userArr['companyId']          = $companyId;
            $userArr['recruiterStatus']    = $recruiterStatus;
            $userArr['chatId']             = $userFairId.'f'.$userId;
            $userFairSetting = Fair::where('id',$userFairId)->first();
            $userArr['ChatApiDetail'] = $this->getCometChatApiDetail($userFairSetting->organiser_id);
        }

        if ($role == 'Organizer') {
            $userSetting = UserSettings::where('user_id',$userId)->select('credits')->first();
            $userArr['chatId']        = $userId;
            $userArr['userCredits']   = $userSetting->credits;
            $userArr['ChatApiDetail'] = $this->getCometChatApiDetail($userId);
        }

        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $userArr
         ]);
    }
}
