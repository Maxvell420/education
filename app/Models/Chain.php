<?php

namespace App\Models;

use App\Events\ChainCreateEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    use HasFactory;

    protected $fillable=['user_id','globalwork_id','course_id','question_id','admin','variable_1','variable_2','variable_3','variable_4','variable_5',
        'variable_6','variable_7','variable_8'];

    public function Course(){
        return $this->belongsTo(Course::class);
    }
    public function Globalwork()
    {
        return $this->belongsTo(Globalwork::class);
    }
    public function Question()
    {
        return $this->belongsTo(Question::class);
    }
}
