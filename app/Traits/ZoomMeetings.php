<?php

namespace App\Traits;
use Illuminate\Http\Request;


trait ZoomMeetings {

  public function setZoomMeeting($topic, $start_time, $duration, $timzone){
    $data = array(
      "topic" => $topic,
      "type" => 1,
      "start_time" => $start_time,
      "duration" => $duration,
      "timezone" => $timzone,
      "settings" => array(
        "host_video" => true,
        "participant_video" => true
      )
    );
    // return json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.zoom.us/v2/users/OaExAB_YSBiFX84WiqlIqg/meetings");
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
      'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6Im1XaFUtXzRoVDY2NVN3cFM4X0FYTWciLCJleHAiOjE2MTU4MDE2ODAsImlhdCI6MTU4MzY2NDE0M30.CJV0QystWKJ4Tj7LhefZcXFEx4fadt6_LLcWJKCV0xE',
      'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $apiResponse = (array)json_decode($apiResponse);
    curl_close ($ch);
    return response()->json($apiResponse);
  }

  public function registerZoomUser($data){
    // return json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.zoom.us/v2/users");
    curl_setopt($ch, CURLOPT_HTTPHEADER,array(
      'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6Im1XaFUtXzRoVDY2NVN3cFM4X0FYTWciLCJleHAiOjE2MTU4MDE2ODAsImlhdCI6MTU4MzY2NDE0M30.CJV0QystWKJ4Tj7LhefZcXFEx4fadt6_LLcWJKCV0xE',
      'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // Receive server response ...
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $apiResponse = (array)json_decode($apiResponse);
    curl_close ($ch);
    return $apiResponse;
  }
}
