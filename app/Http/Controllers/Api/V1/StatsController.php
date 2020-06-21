<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\User;
use App\Company;
use App\CompanyJob;
use App\Fair;
use App\UserSettings;
use App\UserLogs;
use Carbon\Carbon;
use App\Tracking;
use App\AgendaView;
use App\CandidateJob;
use App\ChatTranscript;
use App\MarketingChannel;
use App\FairCandidates;
use App\RecruiterSchedule;
use App\RecruiterScheduleInvite;
use App\RecruiterScheduleBooked;
use App\CompanyStandCount;
use App\Traits\UsersList;
use DB;

class StatsController extends Controller
{
    use UsersList;
    public function index()
    {
        $fairs     = Fair::count();
        $companies = Company::count();
        $compjobs  = CompanyJob::count();
        $users     = User::count();
        $user24hrs = UserLogs::where('created_at', '>=', Carbon::now()->subDay())->count();
        $logs      = UserLogs::select('fairs.name as fname','users.name as uname','user_logs.user_ip',
                                      'user_logs.location','user_logs.device', 'user_logs.browser',
                                      'user_logs.referrer', 'user_logs.u_id', 'user_logs.created_at')
                               ->where('user_logs.created_at', '>=', Carbon::now()->subDay())
                               ->leftJoin('users', 'user_logs.user_id', '=', 'users.id')
                               ->leftJoin('fairs', 'user_logs.fair_id', '=', 'fairs.id')
                               ->get();
        $candidates = $this->registeredCandidates('User');
        $data = [
            'fairs'     => $fairs,
            'users'     => $users,
            'companies' => $companies,
            'compjobs'  => $compjobs,
            'logs'      => $logs,
            'user24hrs' => $user24hrs,
            'registeredCandidates'=> $this->registeredCandidates('User'),
            'activeSession'    => $this->getAdminActiveSession(),
            'previouseSession' => $this->getAdminPreviouseSession(),
            'visits'           => $this->adminVisitStats()
        ];
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data,
        ]);
    }


    public function organizerStats($organizer_id){
      $usedCredits  = Fair::where('organiser_id',$organizer_id)->where('live',1)->count();
      $totalCredits = UserSettings::where('user_id',$organizer_id)->first();
       $data = [
            'fair'          => Fair::where('organiser_id',$organizer_id)->select('id','name','fair_image')->first(),
            'usedCredits'   => $usedCredits,
            'totalCredits'  => empty($totalCredits) ? '': $totalCredits->credits,
            'activeSession'    => $this->getActiveSession($organizer_id),
            'previouseSession' => $this->getPreviouseSession($organizer_id),
            'visits'           => $this->fairVisitStats($organizer_id)
            // 'companies'     => $companies,
            // 'compjobs'      => $compjobs,
            // 'logs'          => $logs,
            // 'user24hrs'     => $user24hrs
        ];
        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data
        ]);
    }

    // Fair Active Vistor
    public function getActiveSession($organizer_id){
        $fairArr = array();
        $fairs = Fair::where('organiser_id',$organizer_id)->get();
        foreach ($fairs as $key => $fair) {
            $fairArr[] = $fair->id;
        }
        $sessions = Tracking::whereIn('fair_id', $fairArr)->where('expiry', '>=', date('Y-m-d H:i:s'))->with('user','fair')->orderBy('updated_at', 'DESC')->get();
        return $sessions;
    }
    // Already Visit the Fair
    public function getPreviouseSession($organizer_id){
        $fairArr = array();
        $fairs = Fair::where('organiser_id',$organizer_id)->get();
        foreach ($fairs as $key => $fair) {
            $fairArr[] = $fair->id;
        }
        $sessions = Tracking::whereIn('fair_id', $fairArr)->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('now -1440 minutes')))->with('user','fair')->orderBy('updated_at', 'DESC')->get();

        return $sessions;
    }

    public function fairVisitStats($organizer_id){
        $data  = [];
        $fairArr = array();
        $fairs = Fair::where('organiser_id',$organizer_id)->get();
        foreach ($fairs as $key => $fair) {
            $fairArr[] = $fair->id;
        }
        $data["page_views_24"] = 0;
        $data["visits_per_day"] = '\'Days\'';
        $data["visits_per_day_value"] = 0;
        $date = new Carbon;
        $data['seven_days'] = Tracking::whereIn('fair_id', $fairArr)->where('created_at','>',$date->subWeek())->select('created_at')->get()->groupBy(function($val) {
                return Carbon::parse($val->created_at)->format('d M');
            });
        foreach($data['seven_days'] as $key => $day){
            $data["visits_per_day"] = $data["visits_per_day"].", '".$key."'";
            $data["visits_per_day_value"] = $data["visits_per_day_value"].", ".count($day);

        }
        $data['past_day'] = Tracking::whereIn('fair_id', $fairArr)->where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('now -1440 minutes')))->orderBy('updated_at', 'DESC')->get();
        $data["sessions_24"] = count($data['past_day']);

        return $data;
    }

    public function recruiterStats($recruiter_id,$fair_id){

        $data = [];
        $data["agendaViewsCount"] = AgendaView::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('view',1)->count();

        $data["jobApplicationsCount"] = CandidateJob::where('fair_id',$fair_id)
                                                      ->whereHas('jobs', function($query) use ($recruiter_id){
                                                       $query->where('recruiter_id',$recruiter_id);
                                                      })->count();

        $data["shortlistedCount"] = AgendaView::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('shortlisted',1)->where('rejected',0)->count();


        $data["rejectedCount"]     = AgendaView::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('shortlisted',0)->where('rejected',1)->count();

        // $data["booked_interviews"]     = RecruiterScheduleBooked::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where(DB::raw("CONCAT(`date`, ' ', `start_time`)"), '>=', date('d-m-Y h:i A'))->count();

        $data["booked_interviews"]    = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status', 'booked')->count();
        $data["pending_invitations"]  = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status', 'pending')->count();
        $data["cancelled_interviews"] = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status', 'canceled')->count();

        $data['available_slots'] =  RecruiterSchedule::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('status','available')->count();

        // $data["booked_interviews"] = RecruiterScheduleBooked::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where(DB::raw("CONCAT(`date`, ' ', `start_time`)"), '>=', date('d-m-Y h:i A'))->count();

        // $data["pending_invitations"]     = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('cancel', 0)
        //                                                             ->whereHas('SlotInfo', function($query) {
        //                                                                  $query->where(DB::raw("CONCAT(`days`, ' ', `start_time`)"), '>=', date('d-m-Y h:i A'));
        //                                                             })->count();

        // $data["cancelled_interviews"]     = RecruiterScheduleInvite::where('recruiter_id',$recruiter_id)->where('fair_id',$fair_id)->where('cancel', 1)
        //                                                             ->whereHas('SlotInfo', function($query) {
        //                                                                  $query->where(DB::raw("CONCAT(`days`, ' ', `start_time`)"), '>=', date('d-m-Y h:i A'));
        //                                                             })->count();

        return response()->json([
            "code"   => 200,
            "status" => "success",
            "data"   => $data
        ]);
    }

    public function registeredCandidates($type){
        $users = User::whereHas('roles', function ($query) use ($type) {
            $query->where('name', '=', $type);
        })->count();

        return $users;
    }

    public function fairRegisteredCandidates($fair_id){
        $labels = [];
        $values = [];
        $candidates =  FairCandidates::where('fair_id','=', $fair_id)->select('created_at')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->groupBy(
                function($val) {
                    return Carbon::parse($val->created_at)->format('Y-m-d');
                });
        $data["per_day"] = '\'Date\'';
        foreach ($candidates as $key => $candidate) {
            array_push($labels,date('d M', strtotime($key)));
            array_push($values,count($candidate));
        }
        $data = [
            'labels'=>$labels,
            'values'=> $values
        ];

        return $data;
    }

    public function fairMarketingStats($fair_id){
        $countArray = [
            'fairChannelStats' => $this->getChannelStats($fair_id,'Facebook')
        ];

        return $countArray; die;
        // if($others_count){
        //     foreach($others_count as $count){
        //         $data["others_count"] = $data["others_count"] + $count->clicks;
        //     }
        // }else{
        //     $data["others_count"] = 0;
        // }
        // if(ReferralsModel::where('company_id','=', $company_id)->exists()){
        //     $data["url"] = ReferralsModel::where('company_id','=', $company_id)->first()->referral_url;
        //     $employees = ReferralUsers::where('company_id','=', $company_id)->get();
        //     if($employees){
        //         foreach ($employees as $employee) {
        //             $referrals = ReferralUsersRegistrations::where('user_id','=', $employee->id)->count();
        //             $data["referral_count"] = $data["referral_count"] + $referrals;
        //         }
        //     }
        // }
    }

    public function getChannelStats($fair_id){
        $count = [];
        $channels = MarketingChannel::select('channel_logo', 'clicks','channel_name')->where('fair_id', '=', $fair_id)->get();
        if ($channels) {
            foreach ($channels as $key => $channel) {
                $count[] = [
                    'channel_logo'   => $channel->channel_logo,
                    'name'   => $channel->channel_name,
                    'clicksCount' => $channel->clicks
                ];
            }
        }
       return $count;
    }

     // Fair Active Vistor
    public function getAdminActiveSession(){
        $sessions = Tracking::where('expiry', '>=', date('Y-m-d H:i:s'))->with('user','fair')->orderBy('updated_at', 'DESC')->get();
        return $sessions;
    }
    // Already Visit the Fair
    public function getAdminPreviouseSession(){
        $sessions = Tracking::where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('now -1440 minutes')))->with('user','fair')->orderBy('updated_at', 'DESC')->get();

        return $sessions;
    }

    public function adminVisitStats(){
        $date = new Carbon;
        $data["page_views_24"] = 0;
        $data["visits_per_day"] = '\'Days\'';
        $data["visits_per_day_value"] = 0;
        $data['seven_days'] = Tracking::where('created_at','>',$date->subWeek())->select('created_at')->get()->groupBy(function($val) {
                return Carbon::parse($val->created_at)->format('d M');
            });
        foreach($data['seven_days'] as $key => $day){
            $data["visits_per_day"] = $data["visits_per_day"].", '".$key."'";
            $data["visits_per_day_value"] = $data["visits_per_day_value"].", ".count($day);

        }
        $data['past_day'] = Tracking::where('updated_at', '>=', date('Y-m-d H:i:s', strtotime('now -1440 minutes')))->orderBy('updated_at', 'DESC')->get();
        $data["sessions_24"] = count($data['past_day']);

        return $data;
    }

    public function fairStats($fair_id){
        $data = [];
        $data['standsCount'] = array();
        $data['totalRegistration']    = UserSettings::where('fair_id',$fair_id)->get()->count();
        $data['jobsApplications']     = CandidateJob::where('fair_id',$fair_id)->get()->count();
        $data['totalJobs']            = CompanyJob::where('fair_id',$fair_id)->get()->count();
        $data['totalCompanies']       = Company::where('fair_id',$fair_id)->get()->count();
        $data['totalShortlist']       = AgendaView::where('fair_id',$fair_id)->where('shortlisted',1)->get()->count();
        $data["chats"]                     = ChatTranscript::where('fair_id',$fair_id)->groupBy('receiver_id')->get()->count();
        $data["messages_exchanged"]        = ChatTranscript::where('fair_id',$fair_id)->get()->count();
        $data['fairCompanyJobs']           = $this->fairCompanyJobs($fair_id);
        $data['fairCompanyChats']          = $this->fairCompanyChats($fair_id);
        $data['fairMarketingStats']        = $this->getChannelStats($fair_id);
        $data['candidatesTournouts']       = FairCandidates::where('fair_id',$fair_id)->where('mainhall',1)->count();
        $data['fairRegisteredCandidates']  = $this->fairRegisteredCandidates($fair_id);
        $standsCount  = CompanyStandCount::where('fair_id', $fair_id)->with('company')->get();
        $data['fairRegisteredCandidatesCount']  = FairCandidates::where('fair_id',$fair_id)->count();
        foreach ($standsCount as $key => $stand) {
          $company_name = $stand->company['company_name'];
          $company_name = strlen($company_name) > 10 ? substr($company_name,0,10)."..." : $company_name;
          if(!isset($data['standsCount'][$company_name])){
            $data['standsCount'][$company_name] = 0;
          }
          $data['standsCount'][$company_name]++;
        }
        $data["scheduling"] = array();
        $companies = Company::where('fair_id',$fair_id)->get();
        foreach ($companies as $key => $company) {
          $recruiter_arr = array();
          $recruiters  = UserSettings::select('user_id')->where('company_id',$company->id)->where('fair_id',$fair_id)->get();
          foreach ($recruiters  as $key => $recruiter) {
            $recruiter_arr[] = $recruiter->user_id;
          }
          $booked_interviews = RecruiterScheduleBooked::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->count();

          $pending_invitations = RecruiterScheduleInvite::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->where('status', 'pending')->count();

          $cancelled_interviews = RecruiterScheduleInvite::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->where('status', 'cancel')->count();

          $data["scheduling"][] = array(
                "id"                   => $company->id,
                "name"                 => $company->company_name,
                "booked_interviews"    => $booked_interviews,
                "pending_invitations"  => $pending_invitations,
                "cancelled_interviews" => $cancelled_interviews
            );


        }



        return $data;
    }

    public function fairCompanyJobs($fair_id){
        $companyJobs = [];
        $fairCompanies = Company::where('fair_id',$fair_id)->get();
        foreach ($fairCompanies as $key => $value) {
            $jobsCount = CompanyJob::where('company_id',$value->id)->count();
            $companyJobs[] = [
                'companyName'=> $value->company_name,
                'jobsCount'  => $jobsCount
            ];
        }

        return $companyJobs;
    }

    public function fairCompanyChats($fair_id){
        $companyChats = [];
        $fairCompanies = Company::where('fair_id',$fair_id)->get();
        foreach ($fairCompanies as $key => $value) {
            $chats = ChatTranscript::where('company_id',$value->id)->groupBy('receiver_id')->get();
            $chatsCount = $chats->count();
            $messagesCount = ChatTranscript::where('company_id',$value->id)->count();
            $companyChats[] = [
                'companyName'   => $value->company_name,
                'chatsCount'    => $chatsCount,
                'messagesCount' => $messagesCount
            ];
        }

        return $companyChats;
    }

    public function marketingStats($fair_id){
        $fair_id = $fair_id;
        $data["referral"] = false;
        $data["channel_name"] = "";
        $data["channel_clicks"] = "";
        $data["pie_chart"] = false;
        $total_clicks = MarketingChannel::where('fair_id', '=', $fair_id)->sum('clicks');
        $channels = MarketingChannel::where('fair_id', '=', $fair_id)->get();
        if($total_clicks > 0){
            foreach ($channels as $key => $channel) {
                if($key == 0){
                    $data["pie_chart"] = true;
                    $data["channel_name"] = '\''.$channel->channel_name.' - '.round($channel->clicks/$total_clicks*100).'%\'';
                    $data["channel_clicks"] = $channel->clicks;
                }else{
                    $data["channel_name"] = $data["channel_name"].", '".$channel->channel_name.' - '.round($channel->clicks/$total_clicks*100)."%'";
                    $data["channel_clicks"] = $data["channel_clicks"].", ".$channel->clicks;
                }
            }
        }else{
            foreach ($channels as $key => $channel) {
                if($key == 0){
                    $data["pie_chart"] = true;
                    $data["channel_name"] = '\''.$channel->channel_name.' - '.round($channel->clicks).'%\'';
                    $data["channel_clicks"] = $channel->clicks;
                }else{
                    $data["channel_name"] = $data["channel_name"].", '".$channel->channel_name.' - '.round($channel->clicks)."%'";
                    $data["channel_clicks"] = $data["channel_clicks"].", ".$channel->clicks;
                }
            }
        }
        $data['organicCount'] = FairCandidates::where('marketing_channel','Organic')->where('fair_id',$fair_id)->count();
        $data["channels"] = $channels;
        $data["facebook"] = false;
        $data["twitter"] = false;
        $data["linkedin"] = false;
        $data["multi_email"] = false;
        $data["referral_count"] = 0;
        if(MarketingChannel::where('channel_name','=', 'Facebook')->where('fair_id', '=', $fair_id)->exists()){
            $data["facebook"] = true;
        }

        if(MarketingChannel::where('channel_name','=', 'Twitter')->where('fair_id', '=', $fair_id)->exists()){
            $data["twitter"] = true;
        }

        if(MarketingChannel::where('channel_name','=', 'Linkedin')->where('fair_id', '=', $fair_id)->exists()){
            $data["linkedin"] = true;
        }

        return $data;
    }

    public function companyStats($company_id){
        $data = [];
        $chatCount = 0;
        $chatMessagesCount = 0;
        $company = Company::find($company_id);
        $fair_id = $company->fair_id;
        $company_name  = $company->company_name;
        $company_image = $company->company_logo;
        $data['applications'] = CandidateJob::whereHas('jobs', function($query) use ($company_id){
            $query->whereCompanyId($company_id);
        })->count();
        // $companyStats['applications'] = $applications;
        $recruiter_arr       = array();
        $data['jobs']        = CompanyJob::where('company_id',$company_id)->count();
        $data['shortlisted'] = AgendaView::where('company_id',$company_id)->where('fair_id',$fair_id)->where('shortlisted', '=', '1')->count();
        $data['fair_id'] = $fair_id;
        $data['company_name']  = $company_name;
        $data['company_image'] = $company_image;
        $recruiters  = $this->getUsers('Recruiter',$company_id);
        // return $recruiters; die;
        if ($recruiters) {
            foreach ($recruiters  as $key => $recruiter) {
            
                $chat          = ChatTranscript::where('company_id',$company_id)->where('fair_id',$fair_id)->where('sender_id',$recruiter['id'])->groupBy('receiver_id')->get();

                $chats         = $chat->count();
                $chatCount     += $chats;
                $chatMessages  = ChatTranscript::where('company_id',$company_id)->where('fair_id',$fair_id)->where('sender_id',$recruiter['id'])->count();

                $chatMessagesCount += $chatMessages;

                $data['recruiterChats'][] = [
                   'name'         => $recruiter['name'],
                   'email'        => $recruiter['email'],
                   'avatar'       => env('VRD_ASSETS_IMAGES_URL').$recruiter['user_setting']['user_image'],
                   'chatMessages' => $chatMessages,
                   'chats'        => $chats
                ];
                $recruiter_arr[] = $recruiter['id'];

            }
        }else{
            $data['recruiterChats'] = [];
        }


        // $data["chats"] = ChatTranscript::where('company_id',$company_id)->where('fair_id',$fair_id)->groupBy('sender_id')->get();
        $data["chat_count"] = $chatCount;
        // $data["chat_exchange_count"] = ChatTranscript::where('company_id',$company_id)->where('fair_id',$fair_id)->groupBy('sender_id')->count();
        $data["chat_exchange_count"] = $chatMessagesCount;
        $data["booked_interviews"] = RecruiterScheduleBooked::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->where(DB::raw("CONCAT(`date`, ' ', `start_time`)"), '>=', date('Y-m-d H:i'))->count();

        $data["pending_invitations"] = RecruiterScheduleInvite::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->where('cancel', 0)
                                                                    ->whereHas('SlotInfo', function($query) {
                                                                         $query->where(DB::raw("CONCAT(`days`, ' ', `start_time`)"), '>=', date('Y-m-d H:i'));
                                                                    })->count();

        $data["cancelled_interviews"] = RecruiterScheduleInvite::whereIn('recruiter_id',$recruiter_arr)->where('fair_id',$fair_id)->where('cancel', 1)
                                                                    ->whereHas('SlotInfo', function($query) {
                                                                         $query->where(DB::raw("CONCAT(`days`, ' ', `start_time`)"), '>=', date('Y-m-d H:i'));
                                                                    })->count();
        $data["standsCount"]  = CompanyStandCount::where('fair_id', $fair_id)->where('company_id',$company_id)->count();

        return $data;

    }

    public function companyListing() {
      $companies = Company::with('fair')->get();
      return response()->json([
          "code"   => 200,
          "status" => "success",
          "data"   => $companies
      ]);
    }

    public function jobListing() {
      $jobs = CompanyJob::with('fair')->get();

      return response()->json([
          "code"   => 200,
          "status" => "success",
          "data"   => $jobs
      ]);
    }

}
