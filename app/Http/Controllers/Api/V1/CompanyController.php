<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($fair_id = '')
    {
        $companies = !empty($fair_id) ? Company::all()->where('fair_id', $fair_id) :  Company::all();;
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
}
