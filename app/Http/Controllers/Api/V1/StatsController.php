<?php

namespace App\Http\Controllers\Api\V1;

use App\Fair;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatsController extends Controller
{
    public function create(){
        dd('test');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $data  = $input['formData'];
        $fair_mobile = '';
        if(isset($data['fair_mobile'])){
            $fair_mobile = $data['fair_mobile'];
        }
        $fair_video = '';
        if(isset($data['fair_video'])){
            $fair_video = $data['fair_video'];
        }
        $fair = Fair::create([
            'presenter_id'=> $data['presenters'],
            'organiser_id'=> $data['organizer'],
            'receptionist_id'=> $data['receptionists'],
            'name'=> $data['fair_name'],
            'short_name'=> $data['short_name'],
            'phone'=> $data['phone'],
            'email'=> $data['email'],
            'fair_image'=> $fair_mobile,
            'fair_video'=> $fair_video,
            'timezone'=> $data['ftimezone'],
            'register_time'=> $data['regdate'],
            'start_time'=> $data['startdate'],
            'end_time'=> $data['enddate'],
            'fair_type'=> $data['ftype'],
            'status'=> 0,
            'website'=> (isset($data['website']))?$data['website']:'',
            'facebook'=> (isset($data['facebook']))?$data['facebook']:'',
            'youtube'=> (isset($data['youtube']))?$data['youtube']:'',
            'twitter'=> (isset($data['twitter']))?$data['twitter']:'',
            'linkedin'=> (isset($data['linkedin']))?$data['linkedin']:'',
            'instagram'=> (isset($data['instagram']))?$data['instagram']:'',
        ]);
        return response()->json($fair, 201);
    }
}
