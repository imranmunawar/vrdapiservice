<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyStand;

class FairMainHallController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
    }

    public function companyStand($company_id = '')
    {
        $companyStand = CompanyStand::find($company_id);
        return response()->json($companyStand);
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
        // Create a new CareerTestAnswer in the database...
        if (CompanyStand::where('company_id',$request->id)->exists()) {
            $updateStand = CompanyStand::where('company_id', $request->id)->update(['stand_top' => $request->stand_top,
                'stand_left' => $request->stand_left]);
                return response()->json(['success' => false,'message' => 'Stand Demision Update Successfully'],200); 
        }else{
            $arr = [
              'company_id' => $request->id,
              'stand_top'  => $request->stand_top,
              'stand_left' => $request->stand_left
            ];
            $companyStand = CompanyStand::create($arr); 
            if (!$companyStand) {
                return response()->json(['success' => false,'message' => 'Stand Demision Not Set Successfully'],200); 
            }
        }
    
        return response()->json(['success' => true,'message' => 'Stand Demision Set Successfully' ],200);
    }

    public function createStandWidth(Request $request)
    {
        // Create a new CareerTestAnswer in the database...
        if (CompanyStand::where('company_id',$request->id)->exists()) {
            $updateStand = CompanyStand::where('company_id', $request->id)->update(['stand_width' => $request->stand_width,
                'stand_height' => $request->stand_height]);
                return response()->json(['success' => false,'message' => 'Stand Dimension Update Successfully'],200); 
        }else{
            $arr = [
              'company_id' => $request->id,
              'stand_width'  => $request->stand_width,
              'stand_height' => $request->stand_height
            ];
            $companyStand = CompanyStand::create($arr); 
            if (!$companyStand) {
                return response()->json(['success' => false,'message' => 'Stand Dimension Not Set Successfully'],200); 
            }
        }
    
        return response()->json(['success' => true,'message' => 'Stand Demision Set Successfully' ],200);
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
        $CareerTestAnswer = companyStand::find($id);
        return response()->json($CareerTestAnswer); 
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
        $CareerTestAnswer  = companyStand::findOrFail($id);
        $CareerTestAnswer->fill($data)->save();
            return response()->json([
               'success' => true,
               'message' => 'Career Test Answer Updated Successfully'
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
        $CareerTestAnswer  = companyStand::findOrFail($id);
        if ($CareerTestAnswer) {
          $deleteCareerTestAnswer = companyStand::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Career Test Answer Delete Successfully'], 200); 
        }
    }
}
