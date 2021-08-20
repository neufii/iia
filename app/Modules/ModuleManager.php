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

        $temporaryDirectory = (new TemporaryDirectory())->create();
        $filename = $temporaryDirectory->path('input.dat');

        $script_type = $part->run_command;

        $processArray = [$script_type, $part->path, $filename];
        $jsonData = json_encode($parameters,JSON_FORCE_OBJECT);
        file_put_contents($filename,$jsonData);
        
        $process = new Process($processArray);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();
        $processedOutput = json_decode($output,true) ? json_decode($output,true) : $output;
        $temporaryDirectory->delete();

        return $processedOutput;
    }
}