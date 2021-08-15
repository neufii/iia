<?php

namespace App\Modules;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class ModuleManager
{
    public static function runProcess($part, ...$parameters){
        $output = null;
        $preprocessData = null;
        $postprocessData = null;

        $partName = $part->name;
        $enablePreprocess = filter_var($part->enable_preprocess, FILTER_VALIDATE_BOOLEAN);
        $enablePostprocess = filter_var($part->enable_postprocess, FILTER_VALIDATE_BOOLEAN);

        if($enablePreprocess){
            switch($partName){
                case 'selector':{
                    $preprocessData = Preprocessor::selector($parameters[0],$parameters[1],$parameters[2]);
                    break;
                }
                case 'generator':{
                    $preprocessData = Preprocessor::generator($parameters[0],$parameters[1]);
                    break;
                }
                case 'question_display':{
                    $preprocessData = Preprocessor::questionDisplay($parameters[0]);
                    break;
                }
                case 'checker':{
                    $preprocessData = Preprocessor::answerChecker($parameters[0],$parameters[1]);
                    break;
                }
                case 'updater':{
                    $preprocessData = Preprocessor::updater($parameters[0],$parameters[1],$parameters[2]);
                    break;
                }
                case 'solution_display':{
                    $preprocessData = Preprocessor::solutionDisplay($parameters[0]);
                    break;
                }
                case 'distance_calculator':{
                    $preprocessData = Preprocessor::distanceCalculator($parameters[0],$parameters[1]);
                    break;
                }
                case 'generator_evaluator':{
                    $preprocessData = Preprocessor::generatorEvaluator($parameters[0],$parameters[1]);
                    break;
                }
            }
        }

        if(!isset($part->path) || !file_exists($part->path)){
            //no script
            return $preprocessData;
        }

        //run script
        if($preprocessData){
            $data = $preprocessData;
        }
        else $data = $parameters;

        $temporaryDirectory = (new TemporaryDirectory())->create();
        $filename = $temporaryDirectory->path('input.dat');

        $script_type = $part->run_command;

        $processArray = [$script_type, $part->path, $filename];
        $jsonData = json_encode($data,JSON_FORCE_OBJECT);
        file_put_contents($filename,$jsonData);
        
        $process = new Process($processArray);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $processedOutput = json_decode($output,true) ? json_decode($output,true) : $output;
        $temporaryDirectory->delete();

        if($enablePostprocess){
            switch($partName){
                case 'selector':{
                    $postprocessData = Postprocessor::selector($processedOutput);
                    break;
                }
                case 'generator':{
                    $postprocessData = Postprocessor::generator($processedOutput);
                    break;
                }
                case 'question_display':{
                    $postprocessData = Postprocessor::questionDisplay($processedOutput);
                    break;
                }
                case 'checker':{
                    $postprocessData = Postprocessor::answerChecker($processedOutput);
                    break;
                }
                case 'updater':{
                    $postprocessData = Postprocessor::updater($processedOutput);
                    break;
                }
                case 'solution_display':{
                    $postprocessData = Postprocessor::solutionDisplay($processedOutput);
                    break;
                }
                case 'distance_calculator':{
                    $postprocessData = Postprocessor::distanceCalculator($processedOutput);
                    break;
                }
                case 'generator_evaluator':{
                    $postprocessData = Postprocessor::generatorEvaluator($processedOutput);
                    break;
                }
            }
        }
        return $enablePostprocess ? $postprocessData : $processedOutput;
    }
}