<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->nullable()->constrained('indicators')->onDelete('set null');
            $table->json('question');
            $table->json('answer');
            $table->json('solution');
            $table->foreignId('generator_id')->constrained('modules')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_instances');
    }
}
