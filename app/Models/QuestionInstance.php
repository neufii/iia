<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionInstance extends Model
{
    use HasFactory;

    //relationship
    public function indicator(){
        return $this->belongsTo('App\Models\Indicator', 'indicator_id', 'id');
    }

    public function history(){
        return $this->belongsToMany('App\Models\Learner', 'history', 'question_id', 'learner_id')
        ->withPivot('answer', 'time_used')
        ->withTimestamps();
    }

    public function statistic(){
        return $this->hasMany('App\Models\QuestionStat', 'id');
    }

    //function
    public static function generate($indicatorId, $preferredLevel=null){
        $generated_question = ModuleManager::runProcess('GENERATOR', getenv('ENABLE_GENERATOR_PREPROCESS'), getenv('ENABLE_GENERATOR_POSTPROCESS'),true,$indicatorId, $preferredLevel);
        
        if(!$generated_question){
            //nothing return from generator
            return null;
        }

        $questionArr = json_decode($generated_question['question'],true);
        
        //store to database
        $question = new QuestionInstance();
        $question->question = json_encode($questionArr);
        $question->answer = $generated_question['answer'];
        $question->solution = $generated_question['solution'];
        $question->indicator_id = $indicatorId;
        $question->save();

        $stat = new QuestionStat();
        $stat->question()->associate($question);
        $stat->save();

        return $question;
    }

    public static function selectQuestion($learner, $indicatorId, $excludeHistory=true, $preferredLevel=null){
        $probCorrect = 0;
        while($probCorrect<= 0.5 || $probCorrect >= 1){
            $probCorrect = stats_rand_gen_normal(0.75,0.1);
        }
        $targetRating = $learner->getRating($indicatorId) - log($probCorrect/(1-$probCorrect));
        //generate only when run out of question
        
        return ['targetRating'=>$targetRating, 'questionInstance' => $TODO];
    }

    public function getDisplayableQuestion(){
        return ModuleManager::runProcess('QUESTION_DISPLAY', getenv('ENABLE_QUESTION_DISPLAY_PREPROCESS'), getenv('ENABLE_QUESTION_DISPLAY_POSTPROCESS'), false, $this->question);
    }

    public function getDisplayableSolution(){
        return ModuleManager::runProcess('SOLUTION_DISPLAY', getenv('ENABLE_SOLUTION_DISPLAY_PREPROCESS'), getenv('ENABLE_SOLUTION_DISPLAY_POSTPROCESS'), false, $this->solution);
    }

    public function check($learnerAnswer){
        if(!$learnerAnswer){
            return false;
        }

        $correctAnswer =  $this->answer;
        $moduleOutput = ModuleManager::runProcess('ANSWER_CHECKER', getenv('ENABLE_ANSWER_CHECKER_PREPROCESS'), getenv('ENABLE_ANSWER_CHECKER_POSTPROCESS'), false, $learnerAnswer, $correctAnswer);

        return filter_var($moduleOutput, FILTER_VALIDATE_BOOLEAN);
    }

    public function addHistory($learner, $learnerAnswer, $timeUsed){
        if($learner){
            $learner->history()->attach($this,['answer' => $learnerAnswer ,'time_used' => $timeUsed]);
        }
    }

    public function updateRating($learner, $isCorrect){   
        $newRatings = ModuleManager::runProcess('INSTANCE_UPDATER', getenv('ENABLE_INSTANCE_UPDATER_PREPROCESS'), getenv('ENABLE_INSTANCE_UPDATER_POSTPROCESS'), false, $this->id, $learner->id, $isCorrect);

        if(!$newRatings){
            $questionRating = $this->statistic->rating;
            $learnerRating = $learner->getRating($this->indicator->id);
            $score = $isCorrect ? 1 : 0;
    
            $questionUncertaincy = 1/(1+(0.05*$this->statistic->total_attempts));
            $learnerUncertaincy = 1/(1+(0.05*$learner->indicators()->where('id',$this->indicator_id)->first()->total_attempts));

            $expectedScore = 1/(1+exp(-($learnerRating-$questionRating)));

            $newQuestionRating = $questionRating + $questionUncertaincy*($expectedScore - $score);
            $newLearnerRating = $learnerRating + $learnerUncertaincy*($score - $expectedScore);

            $newRatings['questionRating'] = $newQuestionRating;
            $newRatings['learnerRating'] = $newLearnerRating;
        }

        $questionStat = $this->statistic;
        $questionStat->rating  = $customRating['questionRating'];
        $questionStat->save();
        $learner->indicators()->sync([$this->indicator_id => [ 'rating' => $customRating['learnerRating']] ], false);

        //TODO: UPDATE INDICATOR STAT
    }

    public function upvote(){
        $stat = $this->statistic;
        $stat->upvotes += 1;
        $stat->save();
    }

    public function downvote(){
        $stat = $this->statistic;
        $stat->downvotes += 1;
        $stat->save();
    }
}
