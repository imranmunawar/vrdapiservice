<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MarketingChannel;
use App\Fair;
use App\MarketingRegistration;
use App\User;
use Session;
use DB;
class MarketingChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $channels = [];
        $marketingChannel = MarketingChannel::all()->where('fair_id',$id);
        if ($marketingChannel->count() > 0) {
            foreach ($marketingChannel as $marketing) {
            $cost_click = 0;
            $cost_con = 0;
            $match50  = '';
            $registration = 0;
            if($marketing->clicks == 0){
                $cost_click = $marketing->cost;
            }else{
                $cost_click =round($marketing->cost/$marketing->clicks,2);
            }
            $count = MarketingRegistration::where('channel_id','=', $marketing->id)->count();
            $count = (int)$count;

            if($count==0){
                $cost_con = $marketing->cost;
            }else{
                $cost_con = round($marketing->cost/$count,2);
            }

            if($count > 0){
                $per= DB::table('marketing_channels')
                    ->join('marketing_registrations', 'marketing_channels.id', '=', 'marketing_registrations.channel_id')
                    ->join('match_jobs', 'marketing_registrations.user_id', '=', 'match_jobs.candidate_id')
                    ->where('marketing_channels.id','=', $marketing->id)
                    ->where('match_jobs.percentage','>', 50)
                    ->select(DB::raw('count(match_jobs.id) as numCount'))
                    ->get();
                $match = $per[0]->numCount/$count;
                if($match > 0){
                    $match50 = number_format($match)."%";
                }else{
                    $match50 = "Less then 1%";
                }
            }else{
                $match50 = 0;
            }

            $registration = $count;

            $channels[] = [
                'id'           =>  $marketing->id,
                'name'         =>  $marketing->channel_name,
                'url'          =>  $marketing->url,
                'clicks'       =>  $marketing->clicks,
                'cost'         =>  $marketing->cost,
                'cost_click'   =>  $cost_click,
                'cost_con'     =>  $cost_con,
                'registration' =>  $registration,
                'match50'      =>  $match50
            ];   
        }
    }
       


        return response()->json($channels);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function channelClicks($fairname, $marketing,  Request $request)
    {
        $fair_data = Fair::where('short_name',$fairname)->first();
        if($fair_data){
            if($marketing == "share-on-facebook"){
                $channel_name = "Facebook";
            }else if($marketing == "share-on-twitter"){
                $channel_name = "Twitter";
            }else if($marketing == "share-on-linkedin"){
                $channel_name = "LinkedIn";
            }else{
                $channel_name = $marketing;
            }
            Session::put('channel_name', $channel_name);
            $fair_id = $fair_data->id;
            $cookie_name = $channel_name."_".$fair_data->id;
            if(!$request->cookie($cookie_name)){
                $marketing = MarketingChannel::where('channel_name',$channel_name)->where('fair_id',$fair_id)->first();
                $click_count = $marketing->clicks + 1;
                MarketingChannel::where('channel_name',$channel_name)->where('fair_id',$fair_id)->update(array( 'clicks' => $click_count ));
                $minutes = 60*24*30;
                //$response = new Response('Virtual Recruitment Days');
                return redirect(env('FRONT_APP_URL').'marketing/'.$fairname.'/'.$channel_name)->withCookie(cookie($cookie_name, 'true', $minutes));
            }

            return redirect(env('FRONT_APP_URL').'marketing/'.$fairname.'/'.$channel_name);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $fair = Fair::find($request->fair_id);
        $fairname = $fair->short_name;
        $channelUrl = url("/marketing/".$fairname."/".$request->channel_name); 
        // Create a new MarketingChannel in the database...
         $MarketingChannel = MarketingChannel::create([
            'fair_id'      => $request->fair_id,
            'channel_name' => $request->channel_name,
            'cost'         => $request->cost,
            'url'          => $channelUrl,
            'clicks'       => '0'
         ]);
        if (!$MarketingChannel) {
            return response()->json(['success' => false,'message' => 'Marketing Channel Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'Marketing Channel Created Successfully' ],200);


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
        $MarketingChannel = MarketingChannel::find($id);
        return response()->json($MarketingChannel); 
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
        $MarketingChannel  = MarketingChannel::findOrFail($id);
        $MarketingChannel->fill($data)->save();
            return response()->json([
               'success' => true,
               'message' => 'Fair Media Updated Successfully'
            ], 200);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $MarketingChannel  = MarketingChannel::findOrFail($id);
        if ($MarketingChannel) {
          $deleteMarketingChannel = MarketingChannel::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Marketing Channel Delete Successfully'], 200); 
        }
    }

   
    public function channelRegisteredCandidates($fair_id,$channel_id){
       $candidatesArr = [];
       $candidates = MarketingRegistration::where('channel_id',$channel_id)->with('candidate','candidateInfo','candidateFairInfo')->get();

       if ($candidates) {
            foreach ($candidates as $key => $value) {
                $candidatesArr[] = [
                    'candidate_id' => $value->user_id,
                    'name'         => $value->candidate->name,
                    'email'        => $value->candidate->email,
                    'name'         => $value->candidate->name,
                    'country'      => $value->candidateInfo->user_country,
                    'city'         => $value->candidateInfo->user_country,
                    'cv'           => $value->candidateInfo->user_country,
                    'phone'        => $value->candidateInfo->phone,
                    'marketing_channel' => $value->candidateFairInfo->marketing_channel,
                    'source'       => $value->candidateFairInfo->source,
                    'status'       => $value->candidateFairInfo->status,
                    'created_at'       => date('d-m-Y', strtotime($value->candidateFairInfo->created_at)),
                    'is_candidate_take_test'  => User::isCandidateTakeTest($fair_id,$value->user_id),
                    'is_candidate_attend_fair'=> User::isCandidateAttendFair($fair_id,$value->user_id),
                ];
            }
       }
      
       return $candidatesArr;
    } 
}
