<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ixudra\Curl\Facades\Curl;
use App\Traits\WebinarEmail;
use App\CompanyWebinar;
use App\Company;
use App\CandidateAgenda;
use App\MatchWebinar;
use App\WebinarQuestionnaire;
use App\Traits\CometChatProTrait;
use DB;

class WebinarController extends Controller
{
    use WebinarEmail,CometChatProTrait;
    /**
     * Display a listing of the resource.
     *SSS
     * @return \Illuminate\Http\Response
     */
    public function index($company_id)
    {
        $webinars = CompanyWebinar::where('company_id',$company_id)->get();
        return response()->json($webinars);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Create a new webinar in the database...
        $webinar = CompanyWebinar::create($request->all());
        if ($webinar) {
           // Create Group On Comet Chat Pro
           $this->createGroupOnCometChatPro(
                $request->fair_id,
                $request->company_id,
                $request->fair_id.'f'.$webinar->id,
                $request->title,
                'public',
                $request->fair_id.'f'.$request->recruiter_id

           );
        }
        if (!$webinar) {
            return response()->json([ 
                'success' => false,
                'message' => 'Webinar Not Created Successfully'
            ],200); 
        }

        return response()->json([ 
            'success' => true, 
            'message' => 'Webinar Created Successfully' 
        ],200);


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
        $webinar       = CompanyWebinar::find($id);
        $webinarComany = Company::find($webinar->company_id)->company_logo;
        $webinar['company_logo'] =  $webinarComany;
        return response()->json($webinar); 
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
        $webinar = CompanyWebinar::findOrFail($id);
        if ($webinar) {
           $this->updateGroupOnCometChatPro(
                $request->fair_id,
                $request->company_id,
                $request->fair_id.'f'.$webinar->id,
                $request->title
           );
        }
        $webinar->fill($data)->save();
        return response()->json([
           'success' => true,
           'message' => 'Webinar Updated Successfully'
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
        DB::beginTransaction();
        try {
            $webinar                  = CompanyWebinar::destroy($id);
            $candidateAgenda          = CandidateAgenda::where('webinar_id',$id)->delete();
            $matchWebinar             = MatchWebinar::where('webinar_id',$id)->delete();
            $webinarQuestionnaire     = WebinarQuestionnaire::where('webinar_id',$id)->delete();

          DB::commit();
          return response()->json([
            'success'   => true,
            'message'   => 'Webinar Deleted Successfully'
          ], 200);
          return Redirect::back();
        } catch (\Exception $e) {
          DB::rollback();
          return response()->json([
           'error'   => true,
           'message' => 'Webinar Not Deleted Successfully'
          ], 401);
        }
    }
}
