<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //create and update new modules
        $generator = new Module();
        $generator->name = 'generator';
        $generator->path = '/Users/neufii/Documents/M.Eng/IIAFramework/app/Modules/Scripts/generator.py';
        $generator->run_command = 'python3';
        $generator->save();
        $generator->compatibleIndicators()->sync([1,2]);

        $checker = new Module();
        $checker->name = 'checker';
        $checker->path = '/Users/neufii/Documents/M.Eng/IIAFramework/app/Modules/Scripts/answerChecker.py';
        $checker->run_command = 'python3';
        $checker->save();
        $checker->compatibleIndicators()->sync([1,2]);

        $questionDisplay = new Module();
        $questionDisplay->name = 'question_display';
        $questionDisplay->path = '/Users/neufii/Documents/M.Eng/IIAFramework/app/Modules/Scripts/questionDisplay.py';
        $questionDisplay->run_command = 'python3';
        $questionDisplay->save();
        $questionDisplay->compatibleIndicators()->sync([1,2]);

        $solutionDisplay = new Module();
        $solutionDisplay->name = 'solution_display';
        $solutionDisplay->path = '/Users/neufii/Documents/M.Eng/IIAFramework/app/Modules/Scripts/solutionDisplay.py';
        $solutionDisplay->run_command = 'python3';
        $solutionDisplay->save();
        $solutionDisplay->compatibleIndicators()->sync([1,2]);
    }
}
