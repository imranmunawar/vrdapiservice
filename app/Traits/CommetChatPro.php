<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;

trait CommetChatPro 
{
  public function createUserOnCommetChatPro($uid,$name,$avatar,$role){
    $data = [];
    if (empty($avatar)) {
        $data = [
            'uid'    => $uid,
            'name'   => $name,
            'role'   => $role == 'Company Admin' ? 'CompanyAdmin' : $role
        ];
    }else{
        if ($role == 'Candidate') {
           $avatar = env('CANDIDATE_AVATAR_URL').$avatar;
        }else{
            $avatar = env('VRD_ASSETS_IMAGES_URL').$avatar;
        }

       $data = [
        'uid'      => $uid,
        'name'     => $name,
        'avatar'   => $avatar,
        'role'     => $role == 'Company Admin' ? 'CompanyAdmin' : $role
       ];  
    }
   
    $response = Curl::to('https://api-eu.cometchat.io/v2.0/users')
        ->withHeader('accept: application/json')
        ->withHeader('apikey: '.env('COMMET_CHAT_API_KEY').'')
        ->withHeader('appid: '.env('COMMET_CHAT_APP_ID').'')
        ->withHeader('content-type: application/json')
        ->withData( $data )
        ->asJson()
        ->post();

    // return true;
  }


  public function updateUserOnCommetChatPro($uid,$name,$avatar,$role){
    $data = [];
    if (empty($avatar)) {
        $data = ['name' => $name];
    }else{
        if ($role == 'Candidate') {
           $avatar = env('CANDIDATE_AVATAR_URL').$avatar;
        }else{
            $avatar = env('VRD_ASSETS_IMAGES_URL').$avatar;
        }
        $data = ['name' => $name,'avatar'=> $avatar];
    }  

    $updateUrlApi = 'https://api-eu.cometchat.io/v2.0/users/'.$uid;
    $response = Curl::to($updateUrlApi)
        ->withHeader('accept: application/json')
        ->withHeader('apikey: '.env('COMMET_CHAT_API_KEY').'')
        ->withHeader('appid: '.env('COMMET_CHAT_APP_ID').'')
        ->withHeader('content-type: application/json')
        ->withData( $data )
        ->asJson()
        ->put();
   }

    public function updateCandidateAvatarOnCommetChatPro($uid,$avatar){
    $data = [
        'avatar'=> env('CANDIDATE_AVATAR_URL').$avatar
    ];
    $updateUrlApi = 'https://api-eu.cometchat.io/v2.0/users/'.$uid;
    $response = Curl::to($updateUrlApi)
        ->withHeader('accept: application/json')
        ->withHeader('apikey: '.env('COMMET_CHAT_API_KEY').'')
        ->withHeader('appid: '.env('COMMET_CHAT_APP_ID').'')
        ->withHeader('content-type: application/json')
        ->withData( $data )
        ->asJson()
        ->put();
   }
}
