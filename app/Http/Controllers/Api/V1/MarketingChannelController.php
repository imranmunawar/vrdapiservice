<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MarketingChannel;

class MarketingChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $MarketingChannel = MarketingChannel::all()->where('fair_id',$id);
        return response()->json($MarketingChannel);
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
        // Create a new MarketingChannel in the database...
         $MarketingChannel = MarketingChannel::create($request->all());
        if (!$MarketingChannel) {
            return response()->json(['success' => false,'message' => 'Marketing Channel Not Created Successfully'],200); 
        }
        
        return response()->json(['success' => true,'message' => 'Marketing Channel Created Successfully' ],200);


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
        $MarketingChannel = MarketingChannel::find($id);
        return response()->json($MarketingChannel); 
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
        $MarketingChannel  = MarketingChannel::findOrFail($id);
        $MarketingChannel->fill($data)->save();
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
        $MarketingChannel  = MarketingChannel::findOrFail($id);
        if ($MarketingChannel) {
          $deleteMarketingChannel = MarketingChannel::destroy($id);
          return response()->json(['success'=>true, 'message'=> 'Marketing Channel Delete Successfully'], 200); 
        }
    }
}
