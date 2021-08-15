<?php

namespace App\Modules;

use App\Models\QuestionInstance;

class Preprocessor
{
    public static function selector($indicatorId, $excludeHistory, $preferredLevel){
    }

    public static function generator($indicatorId, $targetLevel){
    }

    public static function questionDisplay($question){
    }

    public static function answerChecker($learnerAnswer, $correctAnswer){
    }

    public static function updater($questionId, $answer, $timeUsed){
    }

    public static function solutionDisplay($solution){
    }

    public static function distanceCalculator($question1, $question2){
        $tempDict = [
            "choice" => "ignored",
            "plus_key" => "content",
            "min_key" => "content",
            "mul_key" => "content",
            "div_key" => "content",
            "name" => "content",
            "object" => "content",
            "measurement" => "content",
            "other" => "ignored",
            "number" => "content"
        ];

        //process building block
        $arr1 = json_decode($question1,true);
        $item1 = [];
        $arr2 = json_decode($question2,true);
        $item2 = [];

        foreach($arr1 as $arrObj){
            $selectedKey = $tempDict[$arrObj['type']];
            if($selectedKey=="ignored") continue;
            $item1[] = $arrObj[$selectedKey];
        }
        foreach($arr2 as $arrObj){
            $selectedKey = $tempDict[$arrObj['type']];
            if($selectedKey=="ignored") continue;
            $item2[] = $arrObj[$selectedKey];
        }

        //calculate similarity
        $intersect = array_intersect($item1,$item2);
        $total = array_merge($item1, $item2);
        return 1-(count($intersect)/count($total));
    }

    public static function generatorEvaluator($question, $threshold){
    }
}