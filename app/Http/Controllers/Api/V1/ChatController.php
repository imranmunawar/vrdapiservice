<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ChatTranscript;
use App\User;

class ChatController extends Controller
{

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
        $user = User::find($id)->name;

        return response()->json(['user'=>$user,'chats'=>$chats]); 

    }

   
}
