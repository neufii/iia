<?php

namespace App\Helpers;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use App\Models\QuestionInstance;
use App\Models\Indicator;
use App\Models\Learner;
use App\Modules\ModuleManager;

use Spatie\TemporaryDirectory\TemporaryDirectory;

class ReportGenerator{
    public static function evaluateGenerator($indicatorId, $questions = 2000, $threshold = 0.0, $preferredLevel = null, $testGenerator = null){
        $indicator = Indicator::findOrFail($indicatorId);
        if(!isset($testGenerator)){
            $testGenerator = $indicator->compatibleModules()->generator()->active()->latest()->first();
        }

        echo("Generator Evaluation:\n Generator ID: ".$testGenerator->id."\n");
        echo("Start:\t".date("Y-m-d H:i:s")."\n");

        $testQuestionIds = [];
        $firstGeneratedQuestion = 0;

        $temporaryDirectory = (new TemporaryDirectory())->create();
        $filename = $temporaryDirectory->path('distance.dat');

        $fp = fopen($filename, 'a');

        for($i=0;$i<$questions;$i++){
            //prepare question
            $questionI = QuestionInstance::generate($indicator,$preferredLevel,$testGenerator);
            if($i == 0) $firstGeneratedQuestion = $questionI->id;
            $testQuestionIds[] = $questionI->id;

            for($j=0;$j<$questions;$j++){
                //create distance matrix
                if($j == $questions-1){
                    fwrite($fp, '0');
                }
                else if($i == $j || $i < $j){
                    fwrite($fp, '0,');
                }
                else{
                    fwrite($fp, $questionI->getDistance(QuestionInstance::findOrFail($testQuestionIds[$j])));  
                    fwrite($fp, ',');
                }
            }
            fwrite($fp, "\n");
            if($i%4 == 0) echo("question / \t".$i."\r");
            if($i%4 == 1) echo("question - \t".$i."\r");
            if($i%4 == 2) echo("question \\ \t".$i."\r");
            if($i%4 == 3) echo("question - \t".$i."\r");
        }
        fclose($fp);

        //clustering
        $processArray = ['python3', __DIR__."/Scripts/evaluator.py", $filename, $threshold];

        $process = new Process($processArray);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = json_decode($process->getOutput());
        $temporaryDirectory->delete();

        $ids = [];
        foreach($output->sample_ids_in_largest_cluster as $id){
            $ids[] = $id+$firstGeneratedQuestion;
        }

        echo("Finish:\t".date("Y-m-d H:i:s")."\n");
        echo("==== Result ====\n");

        $reportName = "generatorReport".time().".txt";
        $report = fopen(__DIR__."/Reports/GeneratorReport/".$reportName, 'a');
        fwrite($report, "Report Date:\t".date("Y-m-d H:i:s")."\n");
        fwrite($report, "Indicator ID:\t".$indicator->id."\n");  
        fwrite($report, "Generator ID:\t".$testGenerator->id."\n");  
        if($preferredLevel) fwrite($report, "Question Level:\t".$preferredLevel."\n");  
        fwrite($report, "Threshold:\t".$threshold."\n");  
        fwrite($report, "Total Generated Questions:\t".$questions."\n");
        fwrite($report, "\n==== Result ====\n");
        fwrite($report, "Total Clusters:\t".$output->total_clusters."\n");
        fwrite($report, "Average Questions Per Cluster:\t".$output->average_question_per_clusters."\tStandard Deviation:\t".$output->std."\n");
        fwrite($report, "Total Questions in Largest Cluster:\t".$output->questions_in_largest_cluster."\n");
        fwrite($report, "Sample Question Instance in Largest Cluster:\t".implode(", ",$ids)."\n");
        fclose($report);

        return $output;
    }

    public static function systemReport(){
        $reportName = "systemReport".time().".txt";
        $report = fopen(__DIR__."/Reports/SystemReport/".$reportName, 'a');

        $indicators = Indicator::all();
        $totalIndicators = $indicators->count();
        $totalLearners = Learner::all()->count();
        $totalQuestions = QuestionInstance::all()->count();

        fwrite($report, "Report Date:\t".date("Y-m-d H:i:s")."\n");
        fwrite($report, "==== Overall ====\n");  

        fwrite($report, "Total Indicators:\t".$totalIndicators."\n");  
        fwrite($report, "Total Learners:\t".$totalLearners."\n");  
        fwrite($report, "Total Questions:\t".$totalQuestions."\n");

        fwrite($report, "\n==== Indicators ====\n");  
        foreach($indicators as $indicator){
            fwrite($report, "Indicator ID:\t".$indicator->id."\n");  
            fwrite($report, $indicator->name."\n");  
            fwrite($report, "".$indicator->description."\n");  

            $learners = $indicator->learners()->get();
            $totalLearners = $learners->count();
            $ratings = $learners->pluck('pivot.rating');
            $avgRating = $learners->avg('pivot.rating');
            $stdDev = 0;
            foreach($ratings as $rating){
                $stdDev += pow(($rating - $avgRating), 2);
            }
            $stdDev = (float)sqrt($stdDev/$totalLearners);
            fwrite($report, "Total Learners:\t".$totalLearners."\n");
            fwrite($report, "Max Learners' Rating:\t".$learners->max('pivot.rating')."\n");
            fwrite($report, "Min Learners' Rating:\t".$learners->min('pivot.rating')."\n");
            fwrite($report, "Average Learners' Rating:\t".$avgRating."\t Standard Deviation:\t".$stdDev."\n");  
            fwrite($report, "Median Learners' Rating:\t".$learners->median('pivot.rating')."\n");

            $activeGenerators = $indicator->compatibleModules()->generator()->active()->get();
            $totalQuestions = $indicator->questions()->whereIn('generator_id',$activeGenerators->pluck('id'))->count();
            $ratings = $indicator->questions()->whereIn('generator_id',$activeGenerators->pluck('id'))->with('statistic')->get()->pluck('statistic.rating');
            $avgRating = $ratings->avg();
            $totalRating = $ratings->count();
            $stdDev = 0;
            foreach($ratings as $rating){
                $stdDev += pow(($rating - $avgRating), 2);
            }
            $stdDev = (float)sqrt($stdDev/$totalRating);

            fwrite($report, "\nActive Generators ID:\t".implode(",", $activeGenerators->pluck('id')->toArray())."\n");
            fwrite($report, "Total Questions:\t".$totalQuestions."\n");
            fwrite($report, "Max Questions' Rating:\t".$ratings->max()."\n");
            fwrite($report, "Min Questions' Rating:\t".$ratings->min()."\n");
            fwrite($report, "Average Questions' Rating:\t".$avgRating."\t Standard Deviation:\t".$stdDev."\n");  
            fwrite($report, "Median Questions' Rating:\t".$ratings->median()."\n");

            unset($learners,$totalLearners,$totalQuestions,$ratings,$avgRating,$stdDev);

            fwrite($report, "\n\t==== Active Generator ====\n");
            foreach($activeGenerators as $generator){
                fwrite($report, "\tGenerator Id:\t".$generator->id."\n");
                if($generator->isLatest) fwrite($report, "\t**new question instances will be generated from this generator**\n");

                $questions = $indicator->questions()->where('generator_id',$generator->id)->with('statistic')->get();
                $avgUpvotes = $questions->pluck('statistic.upvotes')->avg();
                $avgDownvotes = $questions->pluck('statistic.downvotes')->avg();
                fwrite($report, "\tAverage Upvotes:\t".$avgUpvotes."\n");
                fwrite($report, "\tAverage Downvotes:\t".$avgDownvotes."\n");
                fwrite($report, "\tlevel\t\t\ttotal questions\t\texpected rating\t\tmin\t\tmax\t\taverage\t\tSD\n");
                for($i=0;$i<4;$i++){
                    $levelQuestions = $indicator->questions()->where('generator_id',$generator->id)->whereHas('statistic',function($query) use($i){
                        return $query->where('initial_level',$i+1);
                    })->with('statistic')->get();
                    fwrite($report, "\t".($i+1)."\t\t\t\t".$levelQuestions->count()."\t\t\t\t\t1900-1900\t\t\t300\t\t300\t300.0\t0.0"."\n");
                }
                $noLvQuestions = $indicator->questions()->where('generator_id',$generator->id)->whereHas('statistic',function($query){
                    return $query->whereNull('initial_level');
                })->get();
                fwrite($report, "\tno init lv\t\t".$noLvQuestions->count()."\t\t\t\t1900-1900"."\n");
                fwrite($report, "\ttotal\t\t\t".$questions->count()."\t\t\t\t1900-1900"."\n");

                unset($questions,$avgUpvotes,$avgDownvotes,$generator,$levelQuestions);
                fwrite($report, "\t========\n\n");  
            }
            fwrite($report, "\n================\n\n");  
        }
    }
}