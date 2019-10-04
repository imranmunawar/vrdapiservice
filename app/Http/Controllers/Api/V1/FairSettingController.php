<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FairSetting;

class FairSettingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fair_id = $request->fair_id;
        $fairSetting = FairSetting::where('fair_id', $fair_id);
        if ($fairSetting->count() > 0) {
           $fairSetting->update($request->all());
           return response()->json(['success' => false,'message' => 'Fair Setting Updated Successfully'],200); 
        }else{
            FairSetting::create($request->all());
            return response()->json(['success' => true,'message' => 'Fair Setting Saved Successfully' ],200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $fair_id
     * @return \Illuminate\Http\Response
     */
    public function show($fair_id)
    {
        $fairSetting = FairSetting::where('fair_id',$fair_id)->first();
        return response()->json($fairSetting); 
    }

}
