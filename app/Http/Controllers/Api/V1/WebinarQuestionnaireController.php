<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CareerTest;
use App\WebinarQuestionnaire;

class WebinarQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( $fair_id = '', $webinar_id = '')
    {
        $dataObj = [];
        if (WebinarQuestionnaire::where('webinar_id',$webinar_id)->where('fair_id',$fair_id)->exists()) {
            $dataObj = [
                'webinarQuestionnaire'=> WebinarQuestionnaire::where('webinar_id',$webinar_id)->where('fair_id',$fair_id)->get(),
                'careerTestWithAnswers' => CareerTest::with('answers')->where('fair_id', $fair_id)->get()      
            ];
        }else{
             $dataObj = [
                'careerTestWithAnswers' => CareerTest::with('answers')->where('fair_id', $fair_id)->get()      
            ];
        }
        
        return response()->json($dataObj);

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
        $destory = WebinarQuestionnaire::where('webinar_id', $request->webinar_id)
                   ->where('fair_id',$request->fair_id)->delete();
        $questions = CareerTest::all();  
        foreach($questions as $question){
            $id = $question->id;
            $answers = $request->input("options$id");
            if($answers){
                foreach($answers as $answer){
                    $score = $request->input("score$answer");
                    WebinarQuestionnaire::create(array(
                        'fair_id' => $request->fair_id,
                        'webinar_id' => $request->webinar_id,
                        'test_id' => $id,
                        'answer' => $answer,
                        'score' => $score
                    ));
                }
            }
        }
        return response()->json(
            [
                'success' => true,
                'message' => 'Webinar Questionnaire Set Successfully' 
            ],
        200);

    }

}
