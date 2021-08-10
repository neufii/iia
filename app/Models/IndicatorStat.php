<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndicatorStat extends Model
{
    use HasFactory;

    protected $fillable = ['total_question', 'average_rating', 'variance'];

    //relationship
    public function indicator(){
        return $this->belongsTo('App\Models\Indicator', 'indicator_id', 'id');
    }

}
