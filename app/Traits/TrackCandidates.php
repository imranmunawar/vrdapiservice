<?php 
namespace App\Traits;
use Illuminate\Http\Request;
use App\Fair;
use App\UserSettings;
use App\Tracking;


trait TrackCandidates
{

  public function vistFairCandidates($fair,$request){
    $fair_id = $fair->id;
    if (!empty($request->candidate_id)) {
      UserSettings::where('user_id',$request->candidate_id)->update(['user_timezone'=>$request->timezone]);
    }
    if ($fair_id != $request->fair_id) {
      Tracking::create(array(
        'user_id'    => $request->candidate_id ? $request->candidate_id : '0',
        'ip'         => $request->ip,
        'location'   => $request->location,
        'device'     => $request->platform,
        'browser'    => $request->browser,
        'fair_id'    => $fair->id,
        'u_id'       => $fair_id,
        'expiry'     => date('Y-m-d H:i:s', strtotime('now +2 minutes')),
        'referrer'   => 'Test Reffer',
        'updated_at' => date('Y-m-d H:i:s')
      ));
    }else{
      if (!empty($request->candidate_id)) {
        $checkRecord = Tracking::where('u_id',$fair_id)->where('user_id',$request->candidate_id)->first();
        if ($checkRecord) {
           // $date = date('Y-m-d H:i:s', strtotime('now +2 minutes'));

           $checkRecord->update([
                'expiry'     => date('Y-m-d H:i:s', strtotime('now +2 minutes')),
                'referrer'   => 'Test Reffer',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }else{
           // echo "asdasdasdas"; die;
            Tracking::create(array(
              'user_id'    => $request->candidate_id ? $request->candidate_id : '0',
              'ip'         => $request->ip,
              'location'   => $request->location,
              'device'     => $request->platform,
              'browser'    => $request->browser,
              'fair_id'    => $fair->id,
              'u_id'       => $fair_id,
              'expiry'     => date('Y-m-d H:i:s', strtotime('now +2 minutes')),
              'referrer'   => 'Test Reffer',
              'updated_at' => date('Y-m-d H:i:s')
            ));
        }
      }else{
          // echo date('Y-m-d H:i:s'); die;
            if (Tracking::where('u_id',$fair_id)->where('browser',$request->browser)->where('ip',$request->ip)->exists()) {
              Tracking::where('u_id',$fair_id)->where('browser',$request->browser)->where('ip',$request->ip)->update(array(
                'expiry'     => date('Y-m-d H:i:s', strtotime('now +2 minutes')),
                'referrer'   => 'Test Reffer',
                'updated_at' => date('Y-m-d H:i:s')
              ));
            }else{
                Tracking::create(array(
                'user_id'    => $request->candidate_id ? $request->candidate_id : '0',
                'ip'         => $request->ip,
                'location'   => $request->location,
                'device'     => $request->platform,
                'browser'    => $request->browser,
                'fair_id'    => $fair->id,
                'u_id'       => $fair_id,
                'expiry'     => date('Y-m-d H:i:s', strtotime('now +2 minutes')),
                'referrer'   => 'Test Reffer',
                'updated_at' => date('Y-m-d H:i:s')
              ));
            }
            
        }
    }
    
  }
  
}
