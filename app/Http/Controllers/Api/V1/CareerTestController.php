<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CareerTest;
use App\CareerTestAnswer;

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
        if ($request->question_type == 'Yes No') {
            $CareerTest = CareerTest::create($request->all());
            if (!$this->createAnswer($CareerTest->id)){
                return response()->json(['success' => false,'message' => 'Career Test Not Created Successfully'],200); 
            }
        }else{
            $CareerTest = CareerTest::create($request->all());
            if (!$CareerTest) {
                return response()->json(['success' => false,'message' => 'Career Test Not Created Successfully'],200); 
            }
        }

        return response()->json(['success' => true,'message' => 'Career Test Created Successfully' ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fair_id){
        $careerTest = CareerTest::all()->where('fair_id',$fair_id);
        $questionsArr = [];
        foreach ($careerTest as $key => $value){
            $questionsArr[]=[
                "id"                 =>$value->id,
                "fair_id"            =>$value->fair_id,
                "question"           =>$value->question,
                "short_question"     =>$value->short_question,
                "backoffice_question"=>$value->backoffice_question,
                "question_type"      =>$value->question_type,
                "min_selection"      =>$value->min_selection,
                "max_selection"      =>$value->max_selection,
                "display_order"      =>$value->display_order,
                "answers"            => $this->answers($value->id)
            ];
        }

        return response()->json($questionsArr);
    }

    private function answers($test_id){
        $answers = CareerTestAnswer::all()->where('test_id',$test_id);
        $answersArr = [];
        foreach ($answers as $key => $value){
            $answersArr[] = [
                "id"      => $value->id,
                "test_id" => $value->test_id,
                "answer"  => $value->answer,
                "is_checked"=>false
            ];
        }

        return $answersArr;
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

    public function createAnswer($test_id){
        $answersArr = [ 
            [
                'test_id' => $test_id,
                'answer'  => 'Yes'
            ],
            [
                'test_id' => $test_id,
                'answer'  => 'No'
            ] 
        ];

        foreach ($answersArr as $value) {
            $CareerTestAnswer = CareerTestAnswer::create($value);
        }

        return true;

    }
}
