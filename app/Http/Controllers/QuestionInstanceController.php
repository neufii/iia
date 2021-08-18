<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\QuestionInstance;
use App\Models\Learner;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;

class QuestionInstanceController extends Controller
{
    public function getQuestion($indicatorId, Request $request)
    {
        $params = [
            'excludeHistory' => filter_var($request->input('exclude_history',true), FILTER_VALIDATE_BOOLEAN),
            'preferredLevel' => $request->input('level'),
            'userId' => $request->input('user_id')
        ];

        $learner = Learner::where('user_id',$params['userId'])->first();
        if(!isset($learner)){
            return response()->json(['message' => 'Learner with user id '.$params['userId'].' not found'], 404);
        }

        $indicator = Indicator::findOrFail($indicatorId);

        //check compatible question
        $instanceData = QuestionInstance::selectQuestion($learner, $indicator, $params['excludeHistory'], $params['preferredLevel']);

        $instance=$instanceData['questionInstance'];
        if(!isset($instance)){
            //nothing return from item bank
            //if no level preference, check current level of a learner
            $targetLevel = $instanceData['targetLevel'];
            $instance = QuestionInstance::generate($indicator,$targetLevel);
        }
        if(!isset($instance)){
            //nothing return from generator
            return response()->json(['message' => 'Cannot Generate Question Instance'], 500);
        }

        //generate displayable question
        $script = $instance->getDisplayableQuestion();

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$instance->id.' Retreived',
            'data' => [
                'instance'=> [
                    'id'=> $instance->id,
                    'indicator'=> $instance->indicator,
                    'statistic' => $instance->statistic,
                ], 
                'script'=>$script,
            ]
        ], 200);

    }

    public function submit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required','integer'],
            'answer' => ['required', 'string'],
            'time_used' => ['required', 'integer'],
         ]);
      
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $learner = Learner::where('user_id',$request['user_id'])->first();
        if(!isset($learner)){
            return response()->json(['message' => 'Learner with user id '.$request['user_id'].' not found'], 404);
        }
        
        $question = QuestionInstance::findOrFail($id);

        $isCorrect = $question->check($request['answer']);
        $question->updateRating($learner, $isCorrect);
        $question->addHistory($learner, $request['answer'],$isCorrect, $request['time_used']);

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$question->id.' Updated and an Answer is Checked',
            'data' => [
                'question_id'=> $question->id,
                'is_correct'=> $isCorrect,
                'time_used'=> $request['time_used'],
                'question_rating' => $question->rating,
                'learner_rating' => $learner->getRating($question->indicator()->first()->id)
            ]
        ], 200);
    }

    public function updateFeedback(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => ['required', 'in:upvote,downvote']
         ]);
      
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $question = QuestionInstance::findOrFail($id);

        if($request['action'] == 'upvote') $question->upvote();
        else if($request['action'] == 'downvote') $question->downvote();

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$id.' Feedback Updated',
            'data' => $question->statistic()->first(),
        ], 200);
    }

    public function getSolution($id)
    {
        $question = QuestionInstance::findOrFail($id);

        return Response::json([
            'status' => 'completed',
            'message' => 'Solution of Question ID:'.$id.' Retreived',
            'data' => [
                "script"=>$question->getDisplayableSolution()
            ]
        ], 200);
    }
}
