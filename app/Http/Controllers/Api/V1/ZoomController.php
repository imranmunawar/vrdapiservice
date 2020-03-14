<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ZoomMeetings;

class ZoomController extends Controller
{
  use ZoomMeetings;

  public function getUsers(Request $request){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.zoom.us/v2/users");
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
      'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6Im1XaFUtXzRoVDY2NVN3cFM4X0FYTWciLCJleHAiOjE2MTU4MDE2ODAsImlhdCI6MTU4MzY2NDE0M30.CJV0QystWKJ4Tj7LhefZcXFEx4fadt6_LLcWJKCV0xE'
    ));
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS,'status=active');
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $apiResponse = (array)json_decode($apiResponse);
    curl_close ($ch);
    return $apiResponse;
  }

  public function registerUser(Request $request){
    $data = array(
      "action" => 'create',
      "user_info" => array(
        "email" => $request->email,
        "type" => 1,
        "first_name" => $request->first_name,
        "last_name" => $request->last_name
      )
    );
    return $this->registerZoomUser($data);
  }

  public function setMeeting(Request $request){
    $topic = "Test Meeting";
    $type = 1;
    $start_time = "";
    $duration = 45;
    $timzone = "Europe/London";
    return $this->setZoomMeeting($topic, $start_time, $duration, $timzone);
  }
}
