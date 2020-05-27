<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use Ixudra\Curl\Facades\Curl;
use App\CometChatPro;
use App\Fair;
use App\User;
use App\Company;

trait CometChatProTrait
{

    public function createRecruiterOnCometChatPro($fairId,$uid,$name,$avatar,$role){
        $fair = Fair::where('id',$fairId)->select('organiser_id')->first();
        $this->createUserOnCometChatPro($fair->organiser_id,$uid,$name,$avatar,$role);
    }

    public function createCompanyAdminOnCometChatPro($fairId,$uid,$name,$avatar,$role){
        $fair = Fair::where('id',$fairId)->select('organiser_id')->first();
        $this->createUserOnCometChatPro($fair->organiser_id,$uid,$name,$avatar,$role);
    }

    public function updateUser($fairId,$uid,$name,$avatar,$role){
        $fair = Fair::where('id',$fairId)->select('organiser_id')->first();
        $this->updateUserOnCometChatPro($fair->organiser_id,$uid,$name,$avatar,$role);
    }


    public function updateUserProfileImage($fairId,$uid,$avatar){
        $fair = Fair::where('id',$fairId)->select('organiser_id')->first();
        $this->updateUserAvatarOnCometChatPro($fair->organiser_id,$uid,$avatar);
    }

    public function fairChatApiDetail($fairId,$organizerId)
    {
      $dataArr = [];
      if (empty($fairId) && !empty($organizerId)) {
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();   
      }
      if (empty($organizerId) && !empty($fairId)) {
        $fair           = Fair::where('id',$fairId)->select('organiser_id')->first();
        $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();   
      }
      if ($cometApiDetail) {
        $dataArr = [
          'app_id'       => $cometApiDetail->app_id,
          'api_key'      => $cometApiDetail->api_key,
          'rest_api_key' => $cometApiDetail->rest_api_key,
          'region'       => $cometApiDetail->region
        ];
      }
      return $dataArr;
    }

    // public function createAllAdminsOnCometChat($organizerId){
    //     $admins = User::whereHas('roles', function($q){
    //         $q->where('name', 'Admin');
    //     })->get();

    //     foreach ($admins as $key => $row) {
    //         // echo "";
    //         $this->createUserOnCometChatPro($organizerId,$row->id,$row->name,'','admin');
    //     }
    // }

    public function createUserOnCometChatPro($organizerId,$uid,$name,$avatar,$role){
        $addUserUrl = '';
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
        $appId  = $cometApiDetail->app_id;
        $appKey = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region = $cometApiDetail->region;
        $data = [];
        if (empty($avatar)) {
            $data = [
                'uid'    => $uid,
                'name'   => $name,
                'role'   => $role == 'Company Admin' ? 'CompanyAdmin' : $role
            ];
        }else{

           $avatar = env('VRD_ASSETS_IMAGES_URL').$avatar;
           $data = [
            'uid'      => $uid,
            'name'     => $name,
            'avatar'   => $avatar,
            'role'     => $role == 'Company Admin' ? 'CompanyAdmin' : $role
           ];  
        }

        if ($region == 'eu') {
           $addUserUrl = 'https://api-eu.cometchat.io/v2.0/users';
        }

        if ($region == 'us') {
           $addUserUrl = 'https://api-us.cometchat.io/v2.0/users';
        }
       
        $response = Curl::to($addUserUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$appKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->post();
        // return true;
      }


      public function createCandidateOnCometChatPro($organizerId,$uid,$name,$role){
        $addUserUrl = '';
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
        $appId  = $cometApiDetail->app_id;
        $appKey = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region = $cometApiDetail->region;
        $data = [
          'uid'    => $uid,
          'name'   => $name,
          'role'   => $role 
        ];
        if ($region == 'eu') {
           $addUserUrl = 'https://api-eu.cometchat.io/v2.0/users';
        }

        if ($region == 'us') {
           $addUserUrl = 'https://api-us.cometchat.io/v2.0/users';
        }
       
        $response = Curl::to($addUserUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$appKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->post();
        $response = json_encode($response);
        $response = json_decode((string) $response, true);

        if (array_key_exists('data', $response)) {
          return 'true';
        }else{
          return 'false';
        }
      }



      public function updateUserOnCometChatPro($organizerId,$uid,$name,$avatar,$role){
        $updateUserUrl = '';
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
        $appId  = $cometApiDetail->app_id;
        $appKey = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region = $cometApiDetail->region;
        $data = [];
        if (empty($avatar)) {
            $data = [
                'name'   => $name,
                'role'   => $role == 'Company Admin' ? 'CompanyAdmin' : $role
            ];
        }else{

            if ($role == 'candidate') {
               $avatar = env('CANDIDATE_AVATAR_URL').$avatar;
            }else{
                $avatar = env('VRD_ASSETS_IMAGES_URL').$avatar;
            }

           $data = [
            'name'     => $name,
            'avatar'   => $avatar
           ];  
        }

        if ($region == 'eu') {
           $updateUserUrl = 'https://api-eu.cometchat.io/v2.0/users/'.$uid;
        }

        if ($region == 'us') {
           $updateUserUrl = 'https://api-us.cometchat.io/v2.0/users'.$uid;
        }
       
        $response = Curl::to($updateUserUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$appKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->put();
        // return true;
      }



      public function updateUserAvatarOnCometChatPro($organizerId,$uid,$avatar){
        $updateUserUrl = '';
        $cometApiDetail = CometChatPro::where('organizer_id',$organizerId)->first();
        $appId  = $cometApiDetail->app_id;
        $appKey = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region = $cometApiDetail->region;
        $data = [
            'avatar'=> env('CANDIDATE_AVATAR_URL').$avatar
        ];

        if ($region == 'eu') {
           $updateUserUrl = 'https://api-eu.cometchat.io/v2.0/users/'.$uid;
        }

        if ($region == 'us') {
           $updateUserUrl = 'https://api-us.cometchat.io/v2.0/users'.$uid;
        }
       
        $response = Curl::to($updateUserUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$appKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->put();
        // return true;
      }


      public function createGroupOnCometChatPro($fairId,$companyId,$guid,$name,$type,$recruiterChatId){
        $addGroupUrl    = '';
        $fair           = Fair::where('id',$fairId)->select('organiser_id')->first();
        $company        = Company::where('id',$companyId)->select('company_logo')->first();
        $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();
        $appId      = $cometApiDetail->app_id;
        $appKey     = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region     = $cometApiDetail->region;
        $data = [
          'guid' => $guid,
          'name' => $name,
          'type' => 'public',
          'icon' => env('VRD_ASSETS_IMAGES_URL').$company->company_logo
        ];  

        if ($region == 'eu') {
          $addGroupUrl = 'https://api-eu.cometchat.io/v2.0/groups';
        }

        if ($region == 'us') {
          $addGroupUrl = 'https://api-us.cometchat.io/v2.0/groups';
        }
       
        $response = Curl::to($addGroupUrl)
          ->withHeader('accept: application/json')
          ->withHeader('apikey: '.$restApiKey.'')
          ->withHeader('appid: '.$appId.'')
          ->withHeader('content-type: application/json')
          ->withData( $data )
          ->asJson()
          ->post();
        $response = json_encode($response);
        $response = json_decode((string) $response, true);

        if (array_key_exists('data', $response)) {

            $guid = $response['data']['guid'];
            $data       = [
                'admins'=> [$recruiterChatId]
            ];  
            if ($region == 'eu') {
               $updateGroupUrl = 'https://api-eu.cometchat.io/v2.0/groups/'.$guid.'/members';
            }

            if ($region == 'us') {
               $updateGroupUrl = 'https://api-us.cometchat.io/v2.0/groups/'.$guid.'/members';
            }
            
          $response = Curl::to($updateGroupUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$restApiKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->post();
        }

    }



      public function updateGroupOnCometChatPro($fairId,$companyId,$guid,$name){
        $updateGroupUrl = '';
        $fair           = Fair::where('id',$fairId)->select('organiser_id')->first();
        $company        = Company::where('id',$companyId)->select('company_logo')->first();
        $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();
        $appId      = $cometApiDetail->app_id;
        $appKey     = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region     = $cometApiDetail->region;
        $data = [
            'name' => $name,
            'icon' => env('VRD_ASSETS_IMAGES_URL').$company->company_logo
        ];  

        if ($region == 'eu') {
           $updateGroupUrl = 'https://api-eu.cometchat.io/v2.0/groups/'.$guid;
        }

        if ($region == 'us') {
           $updateGroupUrl = 'https://api-us.cometchat.io/v2.0/groups/'.$guid;
        }
       
        $response = Curl::to($updateGroupUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$restApiKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->put();
        // dd($response);
        // return true;
      }
      



      public function addMemberIntoGroupOnCometChatPro($fairId,$guid,$uid){
        $updateGroupUrl = '';
        $fair           = Fair::where('id',$fairId)->select('organiser_id')->first();
        $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();
        $appId      = $cometApiDetail->app_id;
        $appKey     = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region     = $cometApiDetail->region;
        $data       = [
            'participants'=> [$uid]
        ];  

        if ($region == 'eu') {
           $updateGroupUrl = 'https://api-eu.cometchat.io/v2.0/groups/'.$guid.'/members';
        }

        if ($region == 'us') {
           $updateGroupUrl = 'https://api-us.cometchat.io/v2.0/groups/'.$guid.'/members';
        }
       
        $response = Curl::to($updateGroupUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$restApiKey.'')
            ->withHeader('appid: '.$appId.'')
            ->withHeader('content-type: application/json')
            ->withData( $data )
            ->asJson()
            ->post();
        // dd($response);
        // return true;
      }

      public function removeMemberFromGroupOnCometChatPro($fairId,$guid,$uid){
        $updateGroupUrl = '';
        $fair           = Fair::where('id',$fairId)->select('organiser_id')->first();
        $cometApiDetail = CometChatPro::where('organizer_id',$fair->organiser_id)->first();
        $appId      = $cometApiDetail->app_id;
        $appKey     = $cometApiDetail->api_key;
        $restApiKey = $cometApiDetail->rest_api_key;
        $region     = $cometApiDetail->region;
        $data       = [
            'participants'=> [$uid]
        ];  

        if ($region == 'eu') {
           $updateGroupUrl = 'https://api-eu.cometchat.io/v2.0/groups/'.$guid.'/members/uid/'.$uid;
        }

        if ($region == 'us') {
           $updateGroupUrl = 'https://api-us.cometchat.io/v2.0/groups/'.$guid.'/members/uid'.$uid;
        }
       
        $response = Curl::to($updateGroupUrl)
            ->withHeader('accept: application/json')
            ->withHeader('apikey: '.$restApiKey.'')
            ->withHeader('appid:  '.$appId.'')
            ->withHeader('content-type: application/json')
            ->delete();
        // dd($response);
        // return true;
      }


      // public function updateUserOnCometChatPro($uid,$name,$avatar,$role){
      //   $data = [];
      //   if (empty($avatar)) {
      //       $data = ['name' => $name];
      //   }else{
      //       if ($role == 'Candidate') {
      //          $avatar = env('CANDIDATE_AVATAR_URL').$avatar;
      //       }else{
      //           $avatar = env('VRD_ASSETS_IMAGES_URL').$avatar;
      //       }
      //       $data = ['name' => $name,'avatar'=> $avatar];
      //   }  

      //   $updateUrlApi = 'https://api-eu.cometchat.io/v2.0/users/'.$uid;
      //   $response = Curl::to($updateUrlApi)
      //       ->withHeader('accept: application/json')
      //       ->withHeader('apikey: '.env('COMMET_CHAT_API_KEY').'')
      //       ->withHeader('appid: '.env('COMMET_CHAT_APP_ID').'')
      //       ->withHeader('content-type: application/json')
      //       ->withData( $data )
      //       ->asJson()
      //       ->put();
      //  }

        public function updateCandidateAvatarOnCometChatPro($uid,$avatar){
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


        // public function convertStringToDate($strTime) {
        //     $timestamp = Number($strTime) * 1000;
        //     $date = new Date($timestamp);
        //     $timestr = formatAMPM($date);
        //     return $timestr
        //      // return $timestr.toString();
        // }

        // public function formatAMPM(date) {
        //  $hours   = $date.getHours();
        //  $minutes = $date.getMinutes();
        //  $ampm    = $hours >= 12 ? "pm" : "am";
        //  $hours   = $$hours % 12;
        //  $hours   = $hours ? $hours : 12; // the hour '0' should be '12'
        //  $minutes = $minutes < 10 ? "0" + $minutes : $minutes;
        //  $strTime = $hours + ":" + $minutes + " " + $ampm;
        //  return $strTime;
        // }

}
