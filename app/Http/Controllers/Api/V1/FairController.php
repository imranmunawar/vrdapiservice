<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Fair;
use App\Company;
use App\CompanyJob;
use App\FairSetting;
use App\UserSettings;

class FairController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fairs = Fair::with('organizer')->get();
        return response()->json($fairs);
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
        // Create a new Fair in the database...
        $fair = Fair::create($request->all());
        if (!$fair) {
            return response()->json(['success' => false,'message' => 'Fair Media Not Created Successfully'],200); 
        }
        return response()->json(['success' => true,'message' => 'Fair Media Created Successfully' ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $fair = Fair::find($id);
        return response()->json($fair); 
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $fair = Fair::find($id);
        return response()->json($fair); 
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
        $fair  = Fair::findOrFail($id);
        $fair->fill($data)->save();
        return response()->json([
           'success' => true,
           'message' => 'Fair Updated Successfully'
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
        $fair  = Fair::findOrFail($id);
        if ($fair) {
          $deleteFair = Fair::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Fair Delete Successfully'], 200); 
        }
    }

    public function showFairByShortname(Request $request)
    {
        $short_name = $request->short_name;
        $fair = Fair::where('short_name',$short_name)->first();
        if ($fair) {
            return response()->json($fair);
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Fair Not Found'
            ], 404);
        }     
    }

    public function terms($fair_id)
    {
        $terms = FairSetting::select('terms_conditions')->where('fair_id',$fair_id)->first();
        if ($terms) {
            return response()->json($terms);
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Terms And Condition Not Found'
            ], 404);
        }     
    }

    public function privacy($fair_id)
    {
        $privacy = FairSetting::select('privacy_policy')->where('fair_id',$fair_id)->first();
        if ($privacy) {
            return response()->json($privacy);
        }else{
            return response()->json([
               'error'   => true,
               'message' => 'Privacy Policy Not Found'
            ], 404);
        }     
    }

    public function aboutFair($organizer_id)
    {
        $organizer = UserSettings::where('user_id',$organizer_id)->first();
        if ($organizer) {
            return response()->json(['info'=>$organizer->user_info],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Fair Not Found'
            ], 404);
        }    
    }

    public function exhibitors($fair_id)
    {
        $companies = Company::select('company_logo','company_name')->where('fair_id',$fair_id)->get();
        if ($companies) {
            return response()->json(['companies'=>$companies],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Exhibitors Not Found'
            ], 404);
        }    
    }

    public function jobs($fair_id)
    {
        $jobs = CompanyJob::where('fair_id',$fair_id)->with('company')->get();
        if ($jobs) {
            return response()->json(['jobs'=>$jobs],200);
        }else{
            return response()->json([
               'error' => true,
               'message' => 'Jobs Not Found'
            ], 404);
        }    
    }

}
