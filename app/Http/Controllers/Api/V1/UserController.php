<?php

namespace App\Http\Controllers\Api\V1;
use App\User;
use App\UserSettings;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type)
    {
    	$users = User::whereHas('roles', function ($query) use ($type) {
    	    $query->where('name', '=', $type);
    	})->with('userSetting')->get();
        return response()->json($users);
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
        $data = $request->all(); 
        $user_id = '';
        $role = Role::IsRoleExist($data['role']);
        if($role){
          if ($data['role'] == 'Admin' || $data['role'] == 'Organizer' || $data['role'] == 'Company Admin') {
            $user = User::create([
              'first_name'=> $data['fname'],
              'last_name' => $data['lname'],
              'name'      => $data['fname'].' '.$data['lname'],
              'email'     => $data['email'],
              'password'  => bcrypt($data['password']),
              'plan_password' => $data['password'],
              'user_image'    => array_key_exists('fileToUpload', $data) ? $data['fileToUpload'] : ''
            ]);
            $user->roles()->attach($role);
            $user_id = $user->id;
          }
           
          if ($data['role'] == 'Organizer') {
              $user = UserSettings::create([
                'user_id'          => $user_id,
                'company_name'     => $data['company_name'],
                'credits'          => $data['credits'],
                'reg_notification' => array_key_exists('reg_notification', $data) ? $data['reg_notification'] : 0,
                'enable_exhibitor' => array_key_exists('enable_exhibitor', $data) ? $data['enable_exhibitor'] : 0,
                'user_info'        => $data['user_info']
             ]);
           }

           if ($data['role'] == 'Company Admin') {
               $user = UserSettings::create([
                'user_id'               => $user_id,
                'company_id'            => $data['company_id'],
                'phone'                 => $data['phone'],
                'location'              => $data['location'],
                'user_title'            => $data['title'],
                'user_info'             => $data['user_info'],
              ]);
            }

           if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'User Created Successfully'
                ],200); 
           }else{
                return response()->json([
                   'error' => true,
                   'message' => 'User Not Created Successfully'
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $user,
        ]); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    	$user = User::find($id)->load('userSetting');
        return response()->json($user); 
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
    	$data  = $data['formData'];
		  if ($data['role'] == 'Admin' || $data['role'] == 'Organizer' || $data['role'] == 'Company Admin') {
		  	$user  = User::findOrFail($id);
		    $userDataToUpdate = [
		      'first_name'    => $data['fname'],
		      'last_name'     => $data['lname'],
		      'name'          => $data['fname'].' '.$data['lname'],
		      'email'         => $data['email'],
		      'password'      => bcrypt($data['password']),
              'plan_password' => $data['password'],
		    ];
		    $user->fill($userDataToUpdate)->save();
    	   }
    	  if ($data['role'] == 'Organizer') {
		  	$setting = UserSettings::where('user_id', $id);
		    $settingDataToUpdate = [
		        'company_name'     => $data['company_name'],
		        'credits'          => $data['credits'],
		        'reg_notification' => array_key_exists('reg_notification', $data) ? $data['reg_notification'] : 0,
                'enable_exhibitor' => array_key_exists('enable_exhibitor', $data) ? $data['enable_exhibitor'] : 0,
		        'user_info'        => $data['user_info'],
		    ];
		    $setting->update($settingDataToUpdate);
    	   }

    	   if ($data['role'] == 'Company Admin') {
    	       $setting = UserSettings::where('user_id', $id);
    	       $settingDataToUpdate = [
    	        'company_id' => $data['company_id'],
                'phone'      => $data['phone'],
                'location'   => $data['location'],
                'user_title' => empty($data['title']) ? '' : $data['title'],
                'user_info'  => empty($data['user_info']) ? '' : $data['user_info'],
    	      ];

    	     $setting->update($settingDataToUpdate);
    	    }

    	   if ($user) {
    	        return response()->json([
    	            'success' => true,
    	            'message' => 'User Updated Successfully'
    	        ],200); 
    	   }else{
    	        return response()->json([
    	           'error' => true,
    	           'message' => 'User Not Updated Successfully'
    	        ], 401);
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
        $user  = User::findOrFail($id);
        if ($user) {
          $deleteUser = User::destroy($id);
          $user->roles()->detach(); 
          return response()->json(['success'=>'User Delete Successfully'], 200); 
        }
    }
    public function getUsersByRole($type){
        $users = User::whereHas('roles', function ($query) use ($type) {
            $query->where('name', '=', $type);
        })->get();
        if ($users) {
            return response()->json($users, 200);
        }
    }
}
