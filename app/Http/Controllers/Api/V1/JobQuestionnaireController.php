<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CareerTest;
use App\JobQuestionnaire;

class JobQuestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( $fair_id = '', $job_id = '')
    {
        $dataObj = [];
        if (JobQuestionnaire::where('job_id','=',$job_id)->exists()) {
            $dataObj = [
                'JobQuestionnaire'      => JobQuestionnaire::where('job_id','=', $job_id)->get(),
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
        $destory = JobQuestionnaire::where('job_id', $request->job_id)->delete();
        $questions = CareerTest::all();  
        foreach($questions as $question){
            $id = $question->id;
            $answers = $request->input("options$id");
            if($answers){
                foreach($answers as $answer){
                    $score = $request->input("score$answer");
                    JobQuestionnaire::create(array(
                        'job_id' => $request->job_id,
                        'test_id' => $id,
                        'answer' => $answer,
                        'score' => $score
                    ));
                }
            }
        }
        return response()->json(['success' => true,'message' => 'Job Questionnaire Set Successfully' ],200);


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
