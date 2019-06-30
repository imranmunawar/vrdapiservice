<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyJob;

class CompanyJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($company_id = '')
    {
        $jobs = !empty($company_id) ? CompanyJob::all()->where('company_id', $company_id) :  CompanyJob::all();;
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
         $job = CompanyJOb::create($request->all());
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
                'message' => 'Job Created Successfully' ],200);


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
        $company = CompanyJOb::find($id);
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
        $job = CompanyJOb::findOrFail($id);
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
}
