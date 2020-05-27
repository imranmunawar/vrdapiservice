<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CometChatPro;

class CometChatProController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $fair_id = $request->fair_id;
        $fairChatSetting = CometChatPro::where('fair_id', $fair_id);
        if ($fairChatSetting->count() > 0) {
           $fairChatSetting->update([
            'app_id'  => $request->app_id,
            'api_key' => $request->api_key,
            'region'  => $request->region,
           ]);
           return response()->json(
            [
                'success' => false,
                'message' => 'Fair Chat Detail Updated Successfully'],200); 
        }else{
            CometChatPro::create($request->all());
            return response()->json(
                [
                    'success' => true,
                    'message' => 'Fair Chat Detail Saved Successfully' ],200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fair_id)
    {
        $chat = CometChatPro::where('fair_id',$fair_id)->first();
        if ($chat) {
            return response()->json($chat);
        }else{
            return response()->json([
             'error'    => true,
             'message'  => 'CometChatPro Settings Not Found'
          ], 200);
        }
    }





    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
