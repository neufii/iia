<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionStat extends Model
{
    use HasFactory;
    
    // protected $fillable = ['upvotes', 'downvotes', 'total_attempts', 'correct_attempts', 'average_time_used', 'initial_level', 'rating'];

    //relationship
    public function question(){
        return $this->belongsTo('App\Models\QuestionInstance', 'question_id', 'id');
    }
}
