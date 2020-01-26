<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
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
        echo "Test"; die();
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
  
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $request->user()->load('roles','userSetting')
         ]);
    }
}
