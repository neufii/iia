<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIndicatorLearnerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('indicator_learner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learner_id')->constrained('learners')->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained('indicators')->onDelete('cascade');
            $table->integer('total_attempts')->default(0);
            $table->double('rating')->default(0);
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
        Schema::dropIfExists('indicator_learner');
    }
}
