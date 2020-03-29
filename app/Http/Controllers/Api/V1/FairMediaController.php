<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FairMedia;

class FairMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $fairMedia = FairMedia::where('fair_id',$id)->get();
        return response()->json($fairMedia);
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
        // Create a new FairMedia in the database...

        $FairMedia = FairMedia::create($request->all());
        if (!$FairMedia) {
            return response()->json(['success' => false,'message' => 'FairMedia Media Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'FairMedia Created Successfully' ],200);


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
        $FairMedia = FairMedia::find($id);
        return response()->json($FairMedia); 
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
        $FairMedia  = FairMedia::findOrFail($id);
        $FairMedia->fill($data)->save();
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
        $FairMedia  = FairMedia::findOrFail($id);
        if ($FairMedia) {
          $deleteFairMedia = FairMedia::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Fair Media Delete Successfully'], 200); 
        }
    }
}
