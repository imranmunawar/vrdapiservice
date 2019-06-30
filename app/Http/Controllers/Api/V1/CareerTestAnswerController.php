<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CareerTestAnswer;

class CareerTestAnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $CareerTestAnswer = CareerTestAnswer::all()->where('test_id',$id);
        return response()->json($CareerTestAnswer);
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
         $CareerTestAnswer = CareerTestAnswer::create($request->all());
        if (!$CareerTestAnswer) {
            return response()->json(['success' => false,'message' => 'Career Test Answer Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'Career Test Answer Created Successfully' ],200);


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
        $CareerTestAnswer = CareerTestAnswer::find($id);
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
        $CareerTestAnswer  = CareerTestAnswer::findOrFail($id);
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
        $CareerTestAnswer  = CareerTestAnswer::findOrFail($id);
        if ($CareerTestAnswer) {
          $deleteCareerTestAnswer = CareerTestAnswer::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Career Test Answer Delete Successfully'], 200); 
        }
    }
}
