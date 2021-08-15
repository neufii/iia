<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\QuestionInstance;
use App\Models\Learner;

use Illuminate\Http\Request;
use Response;

class QuestionInstanceController extends Controller
{
    public function getQuestion($indicatorId, Request $request){
        $params = [
            'excludeHistory' => filter_var($request->input('excludeHistory'), FILTER_VALIDATE_BOOLEAN),
            'preferredLevel' => $request->input('level'),
            'userId' => $request->input('userId')
        ];

        $learner = Learner::where('user_id',$params['userId'])->first();
        if(!isset($learner)){
            return Response::json([
                'status' => 'not found',
                'message' => 'Learner with user id '.$params['userId'].' not found',
            ], 404);
        }
        $indicator = Indicator::findOrFail($indicatorId);

        //check compatible question
        $instanceData = QuestionInstance::selectQuestion($learner, $indicator, $params['excludeHistory'], $params['preferredLevel']);

        $instance=$instanceData['questionInstance'];
        if(!$instance){
            //if no level preference, check current level of a learner
            $targetLevel = $instanceData['targetLevel'];
            $instance = QuestionInstance::generate($indicator,$targetLevel);
        }

        //generate displayable question
        $script = $instance->getDisplayableQuestion();

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$instance->id.' Retreived',
            'data' => [
                'instance'=> [
                    'id'=> $instance->id,
                    'statistic' => $instance->statistic,
                ], 
                'script'=>$script,
            ]
        ], 200);

    }

    public function submit(Request $request, $id){
        $validatedRequest = $request->validate([
            'user_id' => ['required','integer'],
            'answer' => ['required', 'string'],
            'time_used' => ['required', 'integer'],
        ]);

        $learner = Learner::where('user_id',$validatedRequest['user_id'])->first();
        if(!isset($learner)){
            return Response::json([
                'status' => 'not found',
                'message' => 'Learner with user id '.$validatedRequest['user_id'].' not found',
            ], 404);
        }
        
        $question = QuestionInstance::findOrFail($id);

        $isCorrect = $question->check($validatedRequest['answer']);
        $question->updateRating($learner, $isCorrect);
        $question->addHistory($learner, $validatedRequest['answer'],$isCorrect, $validatedRequest['time_used']);

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$question->id.' Updated and an Answer is Checked',
            'data' => [
                'question_id'=> $question->id,
                'is_correct'=> $isCorrect,
                'time_used'=> $validatedRequest['time_used'],
                'question_rating' => $question->rating,
                'learner_rating' => $learner->getRating($question->indicator()->first()->id)
            ]
        ], 200);
    }

    public function updateFeedback(Request $request, $id){
        $validatedRequest = $request->validate([
            'action' => ['required', 'in:upvote,downvote']
        ]);

        $question = QuestionInstance::findOrFail($id);

        if($validatedRequest['action'] == 'upvote') $question->upvote();
        else if($validatedRequest['action'] == 'downvote') $question->downvote();

        return Response::json([
            'status' => 'completed',
            'message' => 'Question ID:'.$id.' Feedback Updated',
            'data' => $question->statistic()->first(),
        ], 200);
    }

    public function getSolution($id){
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
