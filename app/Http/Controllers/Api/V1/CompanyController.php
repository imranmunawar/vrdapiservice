<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\MatchJob;
use App\CandidateJob;
use App\MatchRecruiter;
use App\MatchWebinar;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fair_id = '')
    {
        
        $companies = !empty($fair_id) ? Company::where('fair_id',$fair_id)->where('fair_id',$fair_id)->with('stand')->get() :  Company::all();
        return response()->json($companies);
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
         $company = Company::create($request->all());
        if (!$company) {
            return response()->json(
                [ 
                    'success' => false,
                    'message' => 'Company Not Created Successfully'
                ],200); 
        }

        $name  = $request->name;
        $email = $request->email;
        Mail::send('emails.company',['name' => $name, 'email' => $email], 
            function($message) use ($email,$name){
            $message->to($email, $name)->subject('Welcome! '.$name);
        });

        return response()->json(
            [ 
                'success' => true, 
                'message' => 'Company Created Successfully' ],200);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::find($id);
        return response()->json($company); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::find($id);
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
        $company  = Company::findOrFail($id);
        $company->fill($data)->save();
            return response()->json([
               'success' => true,
               'message' => 'Company Updated Successfully'
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
        $company  = Company::findOrFail($id);
        if ($company) {
          $deleteUser = Company::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Company Delete Successfully'], 200); 
        }
    }

    public function candidateCompanyJobs(Request $request){
        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $company_id   = $request->company_id;
        $jobs = MatchJob::where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id)
                        ->where('company_id',$company_id)
                        ->with('jobDetail','companyDetail')
                        ->orderBy('percentage', 'Desc')
                        ->get();
        $candidateAppliedJobs = CandidateJob::where('candidate_id',$candidate_id)
                                ->where('fair_id',$fair_id)->get();

        return response()->json(['jobs'=>$jobs,'appliedJobs'=>$candidateAppliedJobs]);
    }

    public function candidateCompanyRecruiters(Request $request)
    {  
        $recruitersArr = [];
        $fair_id       = $request->fair_id;
        $candidate_id  = $request->candidate_id;
        $company_id    = $request->company_id;
        $recruiters = MatchRecruiter::where('candidate_id',$candidate_id)
                        ->where('fair_id',$fair_id)
                        ->where('company_id',$company_id)
                        ->with('recruiter','companyDetail','recruiterSetting')
                        ->orderBy('percentage', 'Desc')
                        ->get();
        foreach ($recruiters as $row) {
            $recruitersArr[] = [
                "id"         => $row->recruiter_id,
                "company_id" => $row->company_id,
                "fair_id"    => $row->fair_id,
                "percentage" => $row->percentage,
                'name'       => $row->recruiter->name,
                'company_name'   => $row->companyDetail->company_name,
                'title'          => $row->recruiterSetting->user_title,
                'public_email'   => $row->recruiterSetting->public_email,
                'linkedin'       => $row->recruiterSetting->linkedin_profile_link,
                'recruiter_img'  => $row->recruiterSetting->recruiter_img,
                'recruiter_status'  => $row->recruiterSetting->recruiter_status,
                'user_image'     => $row->recruiterSetting->user_image,
                'location'       => $row->recruiterSetting->location,
            ];
        }
        return response()->json(['recruiters'=>$recruitersArr]);
    }

    public function candidateCompanyWebinars(Request $request)
    {  
        $recruitersArr = [];
        $fair_id       = $request->fair_id;
        $candidate_id  = $request->candidate_id;
        $company_id    = $request->company_id;
        $webinars      = MatchWebinar::where('candidate_id',$candidate_id)
                            ->where('fair_id',$fair_id)
                            ->where('company_id',$company_id)
                            ->with('companyWebinar','companyDetail')
                            ->orderBy('percentage', 'Desc')
                            ->get();
        return response()->json(['webinars'=>$webinars]);
    }


    public function companyDetail(Request $request)
    {  
        $company_id = $request->company_id;
        $company = Company::where('id',$company_id)->with('media')->first();
        if ($company) {
            return response()->json($company);
        }

        return response()->json(['error'=>true,'message'=>'company not found'],401);
    }


    public function fairCompanies(Request $request){
        $fairCompanies = Company::where('fair_id',$request->fair_id)->with('stand')->get();
    }

    

}
