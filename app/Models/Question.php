<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable=[
        "course_id","problem","title","incorrect_answer_1","incorrect_answer_2","incorrect_answer_3","question_type","correct_answer","total_attempts"];
    use HasFactory;
    public function globalworks() {
        return $this->hasMany(Globalwork::class);
    }
    public function course(){
        return $this->belongsTo(Course::class);
    }
    public function downloads(){
        return $this->hasOne(Downloads::class);
    }
    public function userAnswer($id=null){
        $query=$this->globalworks()->where("user_id",auth()->user()->id)->where("examine_id",$id);;
        return $query;
    }

}
