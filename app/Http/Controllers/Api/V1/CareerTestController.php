<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CareerTest;

class CareerTestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $CareerTest = CareerTest::all()->where('fair_id',$id);
        return response()->json($CareerTest);
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
        // Create a new CareerTest in the database...
         $CareerTest = CareerTest::create($request->all());
        if (!$CareerTest) {
            return response()->json(['success' => false,'message' => 'Career Test Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'Career Test Created Successfully' ],200);


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
        $CareerTest = CareerTest::find($id);
        return response()->json($CareerTest); 
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
        $CareerTest  = CareerTest::findOrFail($id);
        $CareerTest->fill($data)->save();
            return response()->json([
               'success' => true,
               'message' => 'Fair Media Updated Successfully'
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
        $CareerTest  = CareerTest::findOrFail($id);
        if ($CareerTest) {
          $deleteCareerTest = CareerTest::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Fair Media Delete Successfully'], 200); 
        }
    }
}
