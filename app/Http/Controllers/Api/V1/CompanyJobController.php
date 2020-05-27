<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyJob;
use App\CandidateJob;
use App\MatchJob;
use App\User;
use App\UserSettings;

class CompanyJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company_id = '', $recruiter_id = '')
    {
        $jobs = '';
        if (empty($recruiter_id) && !empty($company_id)) {
            $jobs = CompanyJob::with('applicationsCount')->where('company_id', $company_id)->get(); 
        }elseif (!empty($company_id)) {
           $jobs = CompanyJob::with('applicationsCount')->where('company_id', $company_id)->get(); 
        }
    
        return response()->json($jobs);
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
        // Create a new company in the database...
         $job = CompanyJob::create($request->all());
        if (!$job) {
            return response()->json(
                [ 
                    'success' => false,
                    'message' => 'Job Not Created Successfully'
                ],200); 
        }

        return response()->json(
            [ 
              'success' => true, 
               'message' => 'Job Created Successfully'],200);
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
        $company = CompanyJob::find($id);
        return response()->json($company); 
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
        $job = CompanyJob::findOrFail($id);
        $job->fill($data)->save();
            return response()->json([
               'success' => true,
               'message' => 'Job Updated Successfully'
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
        $job  = CompanyJob::findOrFail($id);
        if ($job) {
          $deleteJob = CompanyJob::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Job Deleted Successfully'], 200); 
        }
    }


    public function jobApplications($job_id){
        $applications = [];
        $candidates = CandidateJob::where('job_id',$job_id)->get();
        // return response()->json($candidates);
        if ($candidates->count() > 0) {
            foreach ($candidates as $row) {
                $match = MatchJob::where('job_id',$row->job_id)->where('candidate_id',$row->candidate_id)->first();
                if (User::where('id', $row->candidate_id)->exists()) {
                   $applications [] = [
                       "job_id"       => $row->job_id,
                       "candidate_id" => $row->candidate_id,
                       "company_id"   => $row->company_id,
                       "fair_id"      => $row->fair_id,
                       "name"         => $row['candidate']['name'],
                       "email"        => $row['candidate']['email'],
                       "user_country" => $row['candidateInfo']['user_country'],
                       "user_city"    => $row['candidateInfo']['user_city'],
                       'cv'           => $row['candidateInfo']['user_cv'],
                       "match"        => $match->percentage
                   ];
                }
            }
            return response()->json(['success' => true, 'applicant'=> $applications], 200);
        }

        return response()->json(['success' => false,'message' => 'Job Candidate Not Found'],200); 
    }


    public function detail($job_id, $candidate_id = '')
    {
        if (empty($candidate_id)) {
            $jobDetail = CompanyJob::where('id',$job_id)->with('company')->first();
            if ($jobDetail) {
                return response()->json(['job'=>$jobDetail],200);
            }else{
                return response()->json([
                   'error'   => true,
                   'message' => 'Job Details Not Found'
                ], 404);
            }
        }else{
            $applied   = false;
            $checkApplied   = CandidateJob::where('candidate_id',$candidate_id)->where('job_id',$job_id)->first();
            $jobDetail = CompanyJob::where('id',$job_id)->with('company')->first();
            if ($jobDetail) {
                if ($checkApplied) {
                  $applied = true;  
                }
                return response()->json(['job'=>$jobDetail,'applied'=>$applied],200);
            }else{
                return response()->json([
                   'error' => true,
                   'message' => 'Job Details Not Found'
                ], 404);
            }
        }    
    }
}
