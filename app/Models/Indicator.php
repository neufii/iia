<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Indicator extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];
    
    //relatioships
    public function questions(){
        return $this->hasMany('App\Models\QuestionInstance', 'id');
    }

    public function learners(){
        return $this->belongsToMany('App\Models\Learner', 'indicator_learner', 'indicator_id', 'learner_id')
        ->withPivot('total_attempts', 'rating')
        ->withTimestamps();
    }
}
