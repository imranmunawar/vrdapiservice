<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CompanyMedia;

class CompanyMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $CompanyMedia = CompanyMedia::all()->where('fair_id',$id);
        return response()->json($CompanyMedia);
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
        // Create a new CompanyMedia in the database...
         $CompanyMedia = CompanyMedia::create($request->all());
        if (!$CompanyMedia) {
            return response()->json(['success' => false,'message' => 'CompanyMedia Media Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'CompanyMedia Created Successfully' ],200);


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
        $CompanyMedia = CompanyMedia::find($id);
        return response()->json($CompanyMedia); 
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
        $CompanyMedia  = CompanyMedia::findOrFail($id);
        $CompanyMedia->fill($data)->save();
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
        $CompanyMedia  = CompanyMedia::findOrFail($id);
        if ($CompanyMedia) {
          $deleteCompanyMedia = CompanyMedia::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Fair Media Delete Successfully'], 200); 
        }
    }
}
