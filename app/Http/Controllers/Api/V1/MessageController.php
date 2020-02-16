<?php

namespace App\Http\Controllers\Api\V1;
use App\Chat;
use App\Message;
use App\MatchRecruiter;
use App\Tracking;
use App\User;
use App\UserSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $candidatesIds = [];
        $getChatMessages = [];
        $user_id = $request->user_id;
        $fair_id = $request->fair_id;
        $candidates = MatchRecruiter::where('fair_id',$fair_id)->where('recruiter_id',$user_id)->get();

        foreach ($candidates as $key => $row) {
           array_push($candidatesIds, $user_id.$row->candidate_id);
        }

        $chats = Chat::whereIn('user_chat_id',$candidatesIds)->get();

        if (count($chats) > 0) {
            foreach ($chats as $key => $chat) {
               $getChatMessages[]=[
                    'chat_id'        => $chat->user_chat_id,
                    "user_last_seen" =>  $this->getUserLastSeen($fair_id,$chat->user_chat_id),
                    'is_user_online' =>  $this->isUserOnline($fair_id,$chat->user_chat_id),
                    'user_name'      =>  $this->getUserName($chat->user_chat_id),
                    'messages'       =>  $this->getChatMessages($chat->user_chat_id)
               ];
            }
        }  

        return $getChatMessages; die();      
    }

    public function getChatMessages($chat_id){
        $messages = [];
        $chat = Message::where('chat_id',$chat_id)->get();
        foreach ($chat as $key => $message) {
            $messages[]=[
                'id'               => $message->id,
                'chat_id'          => $message->chat_id,
                'from_user_id'     => $message->from_user_id,
                'to_user_id'       => $message->to_user_id,
                'user_name'        => $this->getUserName($message->from_user_id),
                'user_image'       => $this->getUserAvatar($message->from_user_id),
                'message'          => $message->message,
                'time'             => \Carbon\Carbon::parse($message->created_at)->diffForHumans(),
                'message_from_role'=> $message->message_from_role,
            ];
        }

        return $messages;
    }

    public function getCandidateMessages($recruiter_id,$candidate_id){
        $messageAndReply= [];
        $userMessages = Message::where('from_user_id',$candidate_id)->get();
        if ($userMessages) {
            foreach ($userMessages as $key => $value) {
                $messageAndReply[]=[
                    'id' => $value->id,
                    'from_user_id' => $value->from_user_id,
                    'to_user_id'   => $value->to_user_id,
                    'message'      => $value->message,
                    'created_at'   => $value->created_at,
                    'reply'        => $this->getMsgReply($recruiter_id,$value->from_user_id)
                ];
            }
        }   

        return $messageAndReply;
    }

    public function getMsgReply($recruiter_id,$candidate_id){
        $reply = Message::where('from_user_id',$recruiter_id)->where('to_user_id',$candidate_id)->get();

        return $reply;
    }

    public function getUserName($id){
        $user = User::find($id);
        if ($user) {
            return $user->name;
        }
        return false;
    }

    public function getUserAvatar($id){
        $user = UserSettings::where('user_id',$id)->first();
        if ($user) {
            return $user->user_image;
        }
        return false;
    }

    public function getUserLastSeen($fair_id,$candidate_id){
        $candidate = Tracking::where('fair_id', $fair_id)->where('user_id',$candidate_id)->orderBy('updated_at', 'DESC')->first();
        $last_seen = \Carbon\Carbon::parse($candidate['updated_at'])->diffForHumans();

        return $last_seen;
    }

    public function isUserOnline($fair_id,$candidate_id){
        $candidate = Tracking::where('fair_id', $fair_id)->where('user_id',$candidate_id)->orderBy('updated_at', 'DESC')->first();
        $interval = strtotime(date('Y-m-d H:i:s')) - strtotime($candidate['updated_at']); 
        $interval = $interval/60;
        // echo $interval; die;
        if ($interval < 5) {
          return 1;
        }
        return 0;
    }

    public function recruiterSendMessage(Request $request){

        $isChat = Chat::where('user_chat_id',$request->chat_id)->first();

        if (!$isChat) {
            Chat::create([
                'user_chat_id' => $request->chat_id,
            ]);
        }

        $createNewMsg = Message::create([
            'chat_id'             => $request->chat_id,
            'from_user_id'        => $request->from_id,
            'to_user_id'          => $request->to_id,
            'message_from_role'   => $request->role,
            'message'             => $request->msg
        ]);


        $chat = [
          'chat_id'        =>  $request->chat_id,
          "user_last_seen" =>  $this->getUserLastSeen($request->fair_id,$request->chat_id),
          'is_user_online' =>  $this->isUserOnline($request->fair_id,$request->chat_id),
          'user_name'      =>  $this->getUserName($request->fair_id,$request->chat_id),
          'messages'       =>  $this->getChatMessages($request->chat_id)
        ];

        return $chat; 

        // if ($createNewMsg) {
        //     return response()->json(['message'=>'Agenda Created Successfully'], 200);
        // }
    }


    public function candidateRecruiterChat(Request $request){
        $isChat = Chat::where('user_chat_id',$request->chat_id)->first();
        if ($isChat) {
            $chat = [
              'chat_id'        =>  $request->chat_id,
              "user_last_seen" =>  $this->getUserLastSeen($request->fair_id,$request->chat_id),
              'is_user_online' =>  $this->isUserOnline($request->fair_id,$request->chat_id),
              'user_name'      =>  $this->getUserName($request->recruiter_id),
              'messages'       =>  $this->getChatMessages($request->chat_id)
            ];
            return $chat;
        }
        return response()->json([
          'success' => false,
          'message' => 'Chat Not Found'
        ], 200);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }
}
