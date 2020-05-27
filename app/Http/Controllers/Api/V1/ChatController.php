<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ChatTranscript;
use App\User;
use App\UserSettings;
use App\Company;
use App\Traits\UsersList;
use App\Traits\CometChatProTrait;
use Ixudra\Curl\Facades\Curl;

class ChatController extends Controller
{

  use UsersList, CometChatProTrait;

  public function userChats(Request $request){
    $chatsToReturn  = []; 
    $company_id    = $request->company_id;
    $fair_id       = $request->fair_id;
    $id            = $request->user_id; 
    $chatApiDetail = $this->fairChatApiDetail($fair_id,'');
    // Send a GET request to: http://www.foo.com/bar with 2 custom headers
    $recruiterId = $fair_id.'f'.$id;
    if ($chatApiDetail['region'] == 'eu') {
      $fetchUrl = 'https://api-eu.cometchat.io/v2.0/users/'.$recruiterId.'/messages';
    }
    if ($chatApiDetail['region'] == 'us') {
      $fetchUrl = 'https://api-us.cometchat.io/v2.0/users/'.$recruiterId.'/messages';
    }

    $response = Curl::to($fetchUrl)
       ->withHeader('appid: '.$chatApiDetail['app_id'].'')
       ->withHeader('apikey: '.$chatApiDetail['rest_api_key'].'')
       ->withHeader('content-type:application/json')
       ->withHeader('accept:application/json')
       ->get();
    $response = json_decode((string) $response, true);
    if (!array_key_exists('error', $response)) {
      if (!empty($response['data'])) {
        // return $response; die;
        foreach ($response['data'] as $key => $data) {
            if(!ChatTranscript::where('id',$data['id'])->exists()){
              $buildMessage = $this->buildMessage($data,$fair_id,$company_id);
              // return $buildMessage."<br>";
              if (!empty($buildMessage)) {
                ChatTranscript::create($buildMessage);
              }
            }              
        }
      }
    }else{
      // echo "in error"; die;
    }

    $chats = ChatTranscript::where('sender_id',$id)->orWhere('receiver_id',$id)->groupBy('receiver_id')->get();
    foreach ($chats as $key => $chat) {

      if($chat['receiver_role'] == 'recruiter' || $chat['receiver_role'] == 'candidate'){
        if($chat['receiver_id'] != $id){
          $avatar = UserSettings::where('user_id',$chat['receiver_id'])->select('user_image')->first();
          if ($chat['receiver_role'] == 'recruiter') {
            $avatar = !empty($avatar->user_image) ? env('VRD_ASSETS_IMAGES_URL').$avatar->user_image : '';
          }
          if ($chat['receiver_role'] == 'candidate') {
            $avatar = !empty($avatar->user_image) ? env('CANDIDATE_AVATAR_URL').$avatar->user_image : '';
          }
          $chatsToReturn[] = [
              'sender_id'      => $chat['sender_id'],
              'receiver_id'    => $chat['receiver_id'],
              'sender_name'    => $chat['sender_name'],
              'receiver_name'  => $chat['receiver_name'],
              'sender_role'    => $chat['sender_role'],
              'receiver_role'  => $chat['receiver_role'],
              'receiver_avatar'=> $avatar 
          ];
        }
      }
    }
    $user  = User::find($id);
    return response()->json(['chats'=>$chatsToReturn,'userName'=>$user->name]);
  }


  public function buildMessage($data,$fair_id,$company_id){
    $messArr = [];
    if ($data['category'] == 'message' && $data['type'] == 'text' && $data['receiverType'] != 'group') {
      $senderEntity   = $data['data']['entities']['sender']['entity'];
      $receiverEntity = $data['data']['entities']['receiver']['entity'];
      $senderId = $data['sender'];
      $receiverId = $data['receiver'];
      if ($senderEntity['role'] == 'recruiter' || $senderEntity['role'] == 'candidate') {
        $lTrim = $fair_id.'f';
        $senderId = ltrim($senderId,$lTrim);
      }
      if ($receiverEntity['role'] == 'recruiter' || $receiverEntity['role'] == 'candidate') {
        $lTrim = $fair_id.'f';
        $receiverId = ltrim($receiverId,$lTrim);
      }
      $messArr = [
        'id'=>$data['id'],
        'sender_id'       =>  $senderId,
        'receiver_id'     =>  $receiverId,
        'category'        =>  $data['category'],
        'type'            =>  $data['type'],
        'sender_role'     =>  $senderEntity['role'],
        'receiver_role'   =>  $receiverEntity['role'],
        'sender_name'     =>  $senderEntity['name'],
        'receiver_name'   =>  $receiverEntity['name'],
        'sender_avatar'   =>  array_key_exists('avatar', $senderEntity) ? $senderEntity['avatar'] : '',
        'receiver_avatar' =>  array_key_exists('avatar', $receiverEntity) ? $receiverEntity['avatar'] : '',
        'message'         =>  $data['data']['text'],
        'sent_at'         =>  $data['sentAt'],
        'fair_id'         =>  $fair_id,
        'company_id'      =>  $company_id
      ];
    }elseif ($data['category'] == 'message' && $data['type'] == 'file' && $data['receiverType'] != 'group') {
      $senderEntity   = $data['data']['entities']['sender']['entity'];
      $receiverEntity = $data['data']['entities']['receiver']['entity'];
      $senderId = $data['sender'];
      $receiverId = $data['receiver'];
      if ($senderEntity['role'] == 'recruiter' || $senderEntity['role'] == 'candidate') {
        $lTrim = $fair_id.'f';
        $senderId = ltrim($senderId,$lTrim);
      }
      if ($receiverEntity['role'] == 'recruiter' || $receiverEntity['role'] == 'candidate') {
        $lTrim = $fair_id.'f';
        $receiverId = ltrim($receiverId,$lTrim);
      }
      $messArr = [
        'id'=>$data['id'],
        'sender_id'       =>  $senderId,
        'receiver_id'     =>  $receiverId,
        'category'        =>  $data['category'],
        'type'            =>  $data['type'],
        'sender_role'     =>  $senderEntity['role'],
        'receiver_role'   =>  $receiverEntity['role'],
        'sender_name'     =>  $senderEntity['name'],
        'receiver_name'   =>  $receiverEntity['name'],
        'sender_avatar'   =>  array_key_exists('avatar', $senderEntity) ? $senderEntity['avatar'] : '',
        'receiver_avatar' =>  array_key_exists('avatar', $receiverEntity) ? $receiverEntity['avatar'] : '',
        'message'         =>  $data['data']['attachments'][0]['url'],
        'extension'       =>  $data['data']['attachments'][0]['extension'],
        'sent_at'         =>  $data['sentAt'],
        'fair_id'         =>  $fair_id,
        'company_id'      =>  $company_id
      ];
    }elseif ($data['category'] == 'call' && $data['type'] == 'audio' || $data['type'] == 'video') {
      $senderEntity   = $data['data']['entities']['by']['entity'];
      $receiverEntity = $data['data']['entities']['for']['entity'];
      $callEntity     = $data['data']['entities']['on']['entity'];
      $senderId   = $data['sender'];
      $receiverId = $data['receiver'];
      if ($senderEntity['role'] == 'recruiter' || $senderEntity['role'] == 'candidate') {
        $lTrim    = $fair_id.'f';
        $senderId = ltrim($senderId,$lTrim);
      }
      if ($receiverEntity['role'] == 'recruiter' || $receiverEntity['role'] == 'candidate') {
        $lTrim = $fair_id.'f';
        $receiverId = ltrim($receiverId,$lTrim);
      }
      $messArr = [
        'id'              =>  $data['id'],
        'sender_id'       =>  $senderId,
        'receiver_id'     =>  $receiverId,
        'category'        =>  $data['category'],
        'type'            =>  $data['type'],
        'sender_role'     =>  $senderEntity['role'],
        'receiver_role'   =>  $receiverEntity['role'],
        'sender_name'     =>  $senderEntity['name'],
        'receiver_name'   =>  $receiverEntity['name'],
        'sender_avatar'   =>  array_key_exists('avatar', $senderEntity) ? $senderEntity['avatar'] : '',
        'receiver_avatar' =>  array_key_exists('avatar', $receiverEntity) ? $receiverEntity['avatar'] : '',
        'message'         =>  $callEntity['status'].' '.$callEntity['type'].' call',
        'sent_at'         =>  $data['sentAt'],
        'fair_id'         =>  $fair_id,
        'company_id'      =>  $company_id
      ];
    }

    return $messArr;

  }


  public function getAllUserChats($fair_id)
  {
    $reArr = [];
    $companies = Company::where('fair_id',$fair_id)->select('id','company_name','fair_id')->get();
    $chatApiDetail = $this->fairChatApiDetail($fair_id,'');
    foreach ($companies as $key => $company) {
      $recruiters = $this->getUsers('Recruiter',$company->id);
      foreach ($recruiters as $key => $recruiter) {
        // Send a GET request to: http://www.foo.com/bar with 2 custom headers
        $recruiterId = $fair_id.'f'.$recruiter['id'];
        if ($chatApiDetail['region'] == 'eu') {
          $fetchUrl = 'https://api-eu.cometchat.io/v2.0/users/'.$recruiterId.'/messages';
        }
        if ($chatApiDetail['region'] == 'us') {
          $fetchUrl = 'https://api-us.cometchat.io/v2.0/users/'.$recruiterId.'/messages';
        }

        $response = Curl::to($fetchUrl)
           ->withHeader('appid: '.$chatApiDetail['app_id'].'')
           ->withHeader('apikey: '.$chatApiDetail['rest_api_key'].'')
           ->withHeader('content-type:application/json')
           ->withHeader('accept:application/json')
           ->get();
        $response = json_decode((string) $response, true);
        if (!array_key_exists('error', $response)) {
          if (!empty($response['data'])) {
            return $response; die;
            foreach ($response['data'] as $key => $data) {
                if(!ChatTranscript::where('id',$data['id'])->exists()){
                  $buildMessage = $this->buildMessage($data,$fair_id,$company->id);
                  // return $buildMessage."<br>";
                  if (!empty($buildMessage)) {
                    ChatTranscript::create($buildMessage);
                  }
                }              
            }
          }
        }else{
          // echo "in error"; die;
        }
      }
    }


    return $reArr;

  }

   
}
