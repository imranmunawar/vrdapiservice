<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Ixudra\Curl\Facades\Curl;
use App\Traits\WebinarEmail;
use App\CompanyWebinar;

class WebinarController extends Controller
{
    use WebinarEmail;
    /**
     * Display a listing of the resource.
     *SSS
     * @return \Illuminate\Http\Response
     */
    public function index($company_id)
    {
        $webinars = CompanyWebinar::where('company_id',$company_id)->get();
        return response()->json($webinars);
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
        // Create a new webinar in the database...
        $webinar = CompanyWebinar::create($request->all());
        if ($webinar) {
            $response = Curl::to('https://api.cometondemand.net/api/v2/createGroup')
            ->withHeader('api-key: 51374xb73fca7c64f3a49d2ffdefbb1f2e8c76')
            ->withData('GUID='.$webinar->id.'&name='.$request->title.'&type=0')
            ->post();
        }
        if (!$webinar) {
            return response()->json([ 
                'success' => false,
                'message' => 'Webinar Not Created Successfully'
            ],200); 
        }

        return response()->json([ 
            'success' => true, 
            'message' => 'Webinar Created Successfully' 
        ],200);


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
        $webinar = CompanyWebinar::find($id);
        return response()->json($webinar); 
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
        $webinar = CompanyWebinar::findOrFail($id);
        if ($webinar) {
            $response = Curl::to('https://api.cometondemand.net/api/v2/deleteGroup')
                ->withHeader('api-key: 51374xb73fca7c64f3a49d2ffdefbb1f2e8c76')
                ->withData('GUID='.$id)
                    ->post();
                $response = Curl::to('https://api.cometondemand.net/api/v2/createGroup')
                ->withHeader('api-key: 51374xb73fca7c64f3a49d2ffdefbb1f2e8c76')
                ->withData('GUID='.$id.'&name='.$request->title.'&type=0')
                    ->post();
        }
        $webinar->fill($data)->save();
        return response()->json([
           'success' => true,
           'message' => 'Webinar Updated Successfully'
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
        $webinar  = CompanyWebinar::findOrFail($id);
        if ($webinar) {
          CompanyWebinar::destroy($id);
          return response()->json([
            'success'=>true, 
            'message'=> 'Webinar Deleted Successfully'
          ], 200); 
        }
    }
}
