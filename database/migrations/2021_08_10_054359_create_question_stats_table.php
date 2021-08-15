<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_instances')->onDelete('cascade');
            $table->integer('initial_level')->nullable();
            $table->double('rating')->default(0);
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->integer('total_attempts')->default(0);
            $table->integer('correct_attempts')->default(0);
            $table->double('average_time_used')->nullable();
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
        Schema::dropIfExists('question_stats');
    }
}
