<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserSettings;
use App\Company;
use App\MatchJob;
use App\CandidateJob;
use App\MatchRecruiter;
use App\MatchWebinar;
use App\CompanyStandCount;
use App\CompanyJob;
use App\CompanyMedia;
use App\CompanyWebinar;
use Illuminate\Support\Facades\Mail;
use DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fair_id = '', $hall_id = '')
    {
       if($hall_id){
          if($hall_id == 'min'){
             $companies = Company::where('fair_id',$fair_id)->limit(1)->orderBy('company_hall', 'ASC')->with('stand')->get();
             $hall_id = $companies[0]['company_hall'];
             $companies = Company::where('fair_id',$fair_id)->where('company_hall',$hall_id)->with('stand')->get();
          }else{
             $companies = Company::where('fair_id',$fair_id)->where('company_hall',$hall_id)->with('stand')->get();
          }
       }else{
          $companies = !empty($fair_id) ? Company::where('fair_id',$fair_id)->with('stand')->get() :  Company::all();
       }
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
        $email = $request->company_email;
        $loginUrl = env('BACKEND_URL').'/login';
        Mail::send('emails.company',['name' => $name, 'email' => $email,'loginUrl'=>$loginUrl],
            function($message) use ($email,$name){
            $message->to($email, $name)->subject('Welcome! '.$name);
        });

        return response()->json(
          [
            'success' => true,
            'message' => 'Company Created Successfully'
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
    public function destroy($id){
      try {
        
        $companyDelete  = Company::destroy($id);
        $companyUsers   = UserSettings::where('company_id',$id)->get();
        if ($companyUsers) {
          foreach ($companyUsers as $key => $user) {
            User::destroy($user->user_id);
          }
        }
        $companyUsersSettings = UserSettings::where('company_id',$id)->delete();
        $companyMedia         = CompanyMedia::where('company_id',$id)->delete();
        $companyMedia         = CompanyJob::where('company_id',$id)->delete();
        $companyWebinar       = CompanyWebinar::where('company_id',$id)->delete();
        $companyStandCount    = CompanyStandCount::where('company_id',$id)->delete();
          DB::commit();
          return response()->json([
            'success'   => true,
            'message'   => 'Company Delete Successfully'
          ], 200);
          return Redirect::back();
        } catch (\Exception $e) {
          DB::rollback();
          return response()->json([
           'error'   => true,
           'message' => 'Company Not Deleted'
          ], 401);
        }
    }

    public function candidateCompanyJobs(Request $request){
        $fair_id      = $request->fair_id;
        $candidate_id = $request->candidate_id;
        $company_id   = $request->company_id;
        if($fair_id > 0 && $candidate_id > 0 && $company_id > 0){
          if(!CompanyStandCount::where('candidate_id', $candidate_id)->where('company_id', $company_id)->where('fair_id', $fair_id)->exists()){
            CompanyStandCount::create(array(
                                  'candidate_id' => $candidate_id,
                                  'company_id' => $company_id,
                                  'fair_id' => $fair_id
                              ));
          }
        }
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
                'company_name'      => $row->companyDetail->company_name,
                'title'             => $row->recruiterSetting->user_title,
                'public_email'      => $row->recruiterSetting->public_email,
                'linkedin'          => $row->recruiterSetting->linkedin_profile_link,
                'recruiter_img'     => $row->recruiterSetting->recruiter_img,
                'recruiter_status'  => $row->recruiterSetting->recruiter_status,
                'user_image'        => $row->recruiterSetting->user_image,
                'location'          => $row->recruiterSetting->location,
                'allow_schedule'    => $row->recruiterSetting->allow_schedule,
                'scheduling_percentage' => $row->recruiterSetting->scheduling_percentage
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
        $nextCompany     = '';
        $preCompany      = '';
        $standBackground = 'r1.jpg';
        $company_id      = $request->company_id;
        $nextCompanyId   = $company_id + 1;
        $preCompanyId    = $company_id - 1;
        $company = Company::where('id',$company_id)->with('media')->first();
        if ($company) {
            $checkNextCompanyId = Company::where('id',$nextCompanyId)->where('fair_id',$request->fair_id)->first();
            $checkPreCompanyId  = Company::where('id',$preCompanyId)->where('fair_id',$request->fair_id)->first();
            if ($checkNextCompanyId) {
               $nextCompany = $checkNextCompanyId->id;
            }
            if ($checkPreCompanyId) {
               $preCompany = $checkPreCompanyId->id;
            }
            if (!empty($company->recruiter_id)) {
              $user = UserSettings::select('recruiter_img')->where('user_id',$company->recruiter_id)->first();
              $standBackground = $user->recruiter_img; 
            }
            return response()->json(['company'=>$company,'preCompany'=>$preCompany,'nextCompany'=>$nextCompany,'standBackground'=>$standBackground]);
        }

        return response()->json(['error'=>true,'message'=>'company not found'],401);
    }

    public function exibitorDetail($company_id)
    {
        $company = Company::select('company_name','company_logo','description','company_web_url')->where('id',$company_id)->first();
        if ($company) {
          return response()->json($company);
        }

        return response()->json(['error'=>true,'message'=>'company not found'],401);
    }


    public function fairCompanies(Request $request){
        $fairCompanies = Company::where('fair_id',$request->fair_id)->with('stand')->get();
    }



}
