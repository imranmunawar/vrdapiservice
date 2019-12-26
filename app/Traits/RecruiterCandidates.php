<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\AgendaView;
use App\MatchRecruiter;
use App\Tracking;
use App\CandidateTest;
use App\User;
use DB;

trait RecruiterCandidates
{
   public function recruiterCandidates(Request $request)
    {
        $fair_id      = $request->fair_id;
        $recruiter_id = $request->recruiter_id;
        $filterBy     = $request->filter;

        if ($filterBy == 'autoEnrolled') {
            $data = $this->autoEnrolledCandidates($fair_id,$recruiter_id);
        }elseif($filterBy == 'shortlisted'){
            $data = $this->shortlistedCandidates($fair_id,$recruiter_id);
        }elseif($filterBy == 'rejected'){
            $data = $this->rejectedCandidates($fair_id,$recruiter_id);
        }elseif($filterBy == 'recentlyViewed'){
            $data = $this->recentlyViewedCandidates($fair_id,$recruiter_id);
        }
        return response()->json($data,200);
    }

    public function resultSet($data,$fair_id){
      $resultSet = [];
      foreach ($data as $key => $row) {
        $resultSet[] = [
              'recruiter_id' => $row->recruiter_id,
              'candidate_id' => $row->candidate_id,
              'percentage'   => $row->percentage,
              'id'           => $row->id,
              'shortlisted'  => $row->shortlisted,
              'rejected'     => $row->rejected,
              'name'         => $row->name,
              'email'        => $row->email,
              'user_skype'   => $row->user_skype,
              'phone'        => $row->phone,
              'user_country' => $row->user_country,
              'notes'        => $row->notes,
              'agenda_viewed' => $row->view,
              'is_candidate_take_test'   => User::isCandidateTakeTest($fair_id,$row->candidate_id),
              'is_candidate_attend_fair' => User::isCandidateAttendFair($fair_id,$row->candidate_id),
              'is_candidate_in_hall'     => User::isCandidateInMainHall($fair_id,$row->candidate_id),
        ];
      }

      return $resultSet;
    }

    public function recentlyViewedCandidates($fair_id,$recruiter_id){
            $data = DB::table('match_recruiters')
            ->where('match_recruiters.recruiter_id',$recruiter_id)
            ->where('match_recruiters.fair_id',$fair_id)
            ->leftJoin('agenda_views', function($join){
                 $join->on('agenda_views.recruiter_id', '=', 'match_recruiters.recruiter_id');
                 $join->on('agenda_views.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->leftJoin('candidate_turnouts', function($join){
                $join->on('candidate_turnouts.fair_id', '=', 'match_recruiters.fair_id');
                $join->on('candidate_turnouts.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->join('users', 'match_recruiters.candidate_id', '=', 'users.id')
            ->join('user_settings', 'match_recruiters.candidate_id', '=', 'user_settings.user_id')
            ->orderBy('agenda_views.updated_at', 'desc')
            ->select('match_recruiters.recruiter_id', 'match_recruiters.candidate_id','match_recruiters.percentage', 'agenda_views.id','agenda_views.view','agenda_views.updated_at', 'agenda_views.shortlisted', 'agenda_views.rejected','agenda_views.notes', 'users.name','users.email', 'user_settings.user_skype', 'user_settings.phone', 'user_settings.user_country', 'candidate_turnouts.id as turnout')
            ->get();  
        $data = $this->resultSet($data,$fair_id);
        return $data;
    }

    public function autoEnrolledCandidates($fair_id,$recruiter_id){
            $data = DB::table('match_recruiters')
            ->where('match_recruiters.recruiter_id',$recruiter_id)
            ->where('match_recruiters.fair_id',$fair_id)
            ->leftJoin('agenda_views', function($join){
                 $join->on('agenda_views.recruiter_id', '=', 'match_recruiters.recruiter_id');
                 $join->on('agenda_views.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->leftJoin('candidate_turnouts', function($join){
                $join->on('candidate_turnouts.fair_id', '=', 'match_recruiters.fair_id');
                $join->on('candidate_turnouts.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->join('users', 'match_recruiters.candidate_id', '=', 'users.id')
            ->join('user_settings', 'match_recruiters.candidate_id', '=', 'user_settings.user_id')
            ->orderBy('match_recruiters.id', 'desc')
            ->select('match_recruiters.recruiter_id', 'match_recruiters.candidate_id','match_recruiters.percentage', 'agenda_views.id', 'agenda_views.view','agenda_views.shortlisted', 'agenda_views.rejected','agenda_views.notes', 'users.name','users.email', 'user_settings.user_skype', 'user_settings.phone', 'user_settings.user_country', 'candidate_turnouts.id as turnout')
            ->get();  
        $data = $this->resultSet($data,$fair_id);
        return $data;
    }

    public function shortlistedCandidates($fair_id,$recruiter_id){
            $data = DB::table('match_recruiters')
            ->where('match_recruiters.recruiter_id',$recruiter_id)
            ->where('match_recruiters.fair_id',$fair_id)
            ->where('agenda_views.shortlisted',1)
            ->leftJoin('agenda_views', function($join){
                 $join->on('agenda_views.recruiter_id', '=', 'match_recruiters.recruiter_id');
                 $join->on('agenda_views.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->leftJoin('candidate_turnouts', function($join){
                $join->on('candidate_turnouts.fair_id', '=', 'match_recruiters.fair_id');
                $join->on('candidate_turnouts.candidate_id', '=', 'match_recruiters.candidate_id');
            })
            ->join('users', 'match_recruiters.candidate_id', '=', 'users.id')
            ->join('user_settings', 'match_recruiters.candidate_id', '=', 'user_settings.user_id')
            ->orderBy('match_recruiters.id', 'desc')
            ->select('match_recruiters.recruiter_id', 'match_recruiters.candidate_id','match_recruiters.percentage', 'agenda_views.id', 'agenda_views.view','agenda_views.shortlisted', 'agenda_views.rejected', 'agenda_views.notes','users.name','users.email', 'user_settings.user_skype', 'user_settings.phone', 'user_settings.user_country', 'candidate_turnouts.id as turnout')
            ->where('agenda_views.shortlisted',1)
            ->get();  

        $data = $this->resultSet($data,$fair_id);
        return $data;
    }

    public function rejectedCandidates($fair_id,$recruiter_id){
        $data = DB::table('match_recruiters')
        ->where('match_recruiters.recruiter_id',$recruiter_id)
        ->where('match_recruiters.fair_id',$fair_id)
        ->where('agenda_views.rejected',1)
        ->leftJoin('agenda_views', function($join){
             $join->on('agenda_views.recruiter_id', '=', 'match_recruiters.recruiter_id');
             $join->on('agenda_views.candidate_id', '=', 'match_recruiters.candidate_id');
        })
        ->leftJoin('candidate_turnouts', function($join){
            $join->on('candidate_turnouts.fair_id', '=', 'match_recruiters.fair_id');
            $join->on('candidate_turnouts.candidate_id', '=', 'match_recruiters.candidate_id');
        })
        ->join('users', 'match_recruiters.candidate_id', '=', 'users.id')
        ->join('user_settings', 'match_recruiters.candidate_id', '=', 'user_settings.user_id')
        ->orderBy('match_recruiters.id', 'desc')
        ->select('match_recruiters.recruiter_id', 'match_recruiters.candidate_id','match_recruiters.percentage', 'agenda_views.id', 'agenda_views.view','agenda_views.shortlisted', 'agenda_views.rejected', 'users.name','users.email', 'user_settings.user_skype', 'user_settings.phone', 'user_settings.user_country','agenda_views.notes', 'candidate_turnouts.id as turnout')
        ->where('agenda_views.rejected',1)
            ->get();  
        $data = $this->resultSet($data,$fair_id);
        return $data;
    }

    public function recruiterAction(Request $request){
      $fair_id      = $request->fair_id;
      $recruiter_id = $request->recruiter_id;
      $candidate_id = $request->candidate_id;
      $company_id   = $request->company_id;
      if(MatchRecruiter::where('recruiter_id', '=', $recruiter_id)->where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->exists()){
        $percentage = MatchRecruiter::where('recruiter_id', '=', $recruiter_id)->where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->first()->percentage;
      }else{
        $percentage = 0;
      }
     if($request->action == 'Shortlist'){
      $notes = $request->notes;
      if(AgendaView::where('fair_id', '=', $fair_id)->where('candidate_id', '=', $candidate_id)->where('recruiter_id', '=', $recruiter_id)->exists()){
        AgendaView::where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->where('recruiter_id', '=', $recruiter_id)->update(array('shortlisted' => 1, 'rejected' => 0, 'notes' => $notes));
      }else{
        AgendaView::create(array(
          'recruiter_id' => $recruiter_id,
          'candidate_id' => $candidate_id,
          'fair_id' => $fair_id,
          'company_id' => $company_id,
          'percentage' => $percentage,
          'shortlisted' => 1,
          'rejected' => 0,
          'notes' => $notes
        ));
      }
        return response()->json([
          'success' => true,
          'message' => 'Candidate Successfully Added to Shortlist'
        ], 200);
    }else if($request->action == 'unShortlist'){
      if(AgendaView::where('fair_id', '=', $fair_id)->where('candidate_id', '=', $candidate_id)->where('recruiter_id', '=', $recruiter_id)->exists()){
        AgendaView::where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->where('recruiter_id', '=', $recruiter_id)->update(array('shortlisted' => 0, 'rejected' => 0));
      }else{
        AgendaView::create(array(
          'recruiter_id' => $recruiter_id,
          'candidate_id' => $candidate_id,
          'fair_id' => $fair_id,
          'company_id' => $company_id,
          'percentage' => $percentage,
          'shortlisted' => 0,
          'rejected' => 0
        ));
      }
        return response()->json([
          'success' => true,
          'message' => 'Candidate Successfully Removed From Shortlist'
        ], 200);
    }else if($request->action == 'Reject'){
      if(AgendaView::where('fair_id', '=', $fair_id)->where('candidate_id', '=', $candidate_id)->where('recruiter_id', '=', $recruiter_id)->exists()){
        AgendaView::where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->where('recruiter_id', '=', $recruiter_id)->update(array('shortlisted' => 0, 'rejected' => 1));
      }else{
        AgendaView::create(array(
          'recruiter_id' => $recruiter_id,
          'candidate_id' => $candidate_id,
          'fair_id' => $fair_id,
          'company_id' => $company_id,
          'percentage' => $percentage,
          'shortlisted' => 0,
          'rejected' => 1
        ));
      }
      return response()->json([
        'success' => true,
        'message' => 'Candidate Successfully Added to Reject List'
      ], 200);

    }else if($request->action == 'unReject'){
      if(AgendaView::where('fair_id', '=', $fair_id)->where('candidate_id', '=', $candidate_id)->where('recruiter_id', '=', $recruiter_id)->exists()){
        AgendaView::where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->where('recruiter_id', '=', $recruiter_id)->update(array('shortlisted' => 0, 'rejected' => 0));
      }else{
        AgendaView::create(array(
          'recruiter_id' => $recruiter_id,
          'candidate_id' => $candidate_id,
          'fair_id' => $fair_id,
          'company_id' => $company_id,
          'percentage' => $percentage,
          'shortlisted' => 0,
          'rejected' => 0
        ));
      }
      return response()->json([
        'success' => true,
        'message' => 'Candidate Successfully  Removed From Reject Listt'
      ], 200);
    }
  }


  public function recruiterOnlineCandidates(Request $request){
      $candidatesArr = [];
      $fair_id      = $request->fair_id;
      $company_id   = $request->company_id;
      $recruiter_id = $request->recruiter_id;
      $candidates = Tracking::where('fair_id', $fair_id)->where('user_id','>',0)->orderBy('updated_at', 'DESC')->get();

      $filtered = $candidates->filter(function ($value, $key) {
        $interval = strtotime(date('Y-m-d H:i:s')) - strtotime($value->updated_at); 
        $interval = $interval/60;
        // echo $interval; die;
        if ($interval < 5) {
          // echo "asdasdas"; die;
          return $value;
        }
        // return $interval;
        
      });
      $matched = array();
      foreach ($filtered as $key => $value) {
        // return $value->user_id; die;
        $candidate_questionnaires = CandidateTest::where('candidate_id','=', $value->user_id)->where('fair_id', '=',$fair_id)->count();
        if($candidate_questionnaires > 0){
          $matchRecr = MatchRecruiter::where('recruiter_id', '=', $recruiter_id)->where('candidate_id', '=', $value->user_id)->where('fair_id', '=', $fair_id)->with('candidate','candidateSetting')->first();
          $agenda = AgendaView::where('recruiter_id', '=', $matchRecr->recruiter_id)->where('candidate_id', '=', $matchRecr->candidate_id)->where('fair_id', '=', $matchRecr->fair_id)->where('shortlisted',0)->where('rejected',0)->first();
          if ($agenda) {
            $arr = [
              'candidate_id' => $matchRecr->candidate_id,
              'recruiter_id' => $matchRecr->recruiter_id,
              'company_id'   => $matchRecr->company_id,
              'fair_id'      => $matchRecr->fair_id,
              'percentage'   => $matchRecr->percentage,
              'name'         => $matchRecr->candidate->name,
              'email'        => $matchRecr->candidate->email,
              'name'         => $matchRecr->candidate->name,
              'country'      => $matchRecr->candidateSetting->user_country,
              'last_seen'    => \Carbon\Carbon::parse($value->updated_at)->diffForHumans(),
              'is_candidate_take_test'   => User::isCandidateTakeTest($fair_id,$matchRecr->candidate_id),
              'is_candidate_attend_fair' => User::isCandidateAttendFair($fair_id,$matchRecr->candidate_id),
              'is_candidate_in_hall'     => User::isCandidateInMainHall($fair_id,$matchRecr->candidate_id),
              'agenda_viewed'            => $agenda->view
            ];
            array_push($matched,$arr);
          }
        }
      }

      return $matched;

  }

  public function postAgendaView(Request $request){
      $fair_id      = $request->fair_id;
      $recruiter_id = $request->recruiter_id;
      $candidate_id = $request->candidate_id;
      $company_id   = $request->company_id;
      if(AgendaView::where('fair_id', '=', $fair_id)->where('candidate_id', '=', $candidate_id)->where('recruiter_id', '=', $recruiter_id)->exists()){
        AgendaView::where('candidate_id', '=', $candidate_id)->where('fair_id', '=', $fair_id)->where('recruiter_id', '=', $recruiter_id)->update(array('view' => 1));
          return response()->json(['message'=>'Agenda updated Successfully'], 200);
      }else{
        AgendaView::create(array(
          'recruiter_id' => $recruiter_id,
          'candidate_id' => $candidate_id,
          'fair_id'      => $fair_id,
          'company_id'   => $company_id,
          'percentage'   => $percentage,
          'shortlisted'  => 0,
          'rejected' => 0,
          'view'     => 1
        ));

        return response()->json(['message'=>'Agenda Created Successfully'], 200);
      }
    }


}
