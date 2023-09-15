<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable=[
        "course_id","problem","title","answer_1","answer_2","answer_3","question_type","answer_4","total_attempts",'correct_answer'];
    use HasFactory;
    public function setCorrectAnswerAttribute($value)
    {
        $this->attributes['correct_answer']='answer_'.$value;
    }
    public function globalworks() {
        return $this->hasMany(Globalwork::class);
    }
    public function course(){
        return $this->belongsTo(Course::class);
    }
    public function downloads(){
        return $this->hasOne(Downloads::class);
    }
    public function userAnswer(int $user_id,int $examine_id=null)
    {
        $query=$this->globalworks()->where("user_id",$user_id)->where("examine_id",$examine_id);;
        return $query;
    }

}
